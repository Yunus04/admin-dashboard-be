<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\AuthResource;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class AuthController extends Controller
{
    protected AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Register a new user.
     *
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Create a new user account. Only Super Admin can register new users.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"super_admin","admin","merchant"}, example="merchant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AuthResource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authRepository->create($data);

        // Create access token (short-lived: 1 hour)
        $token = $user->createToken('auth-token', ['*'], now()->addHour());
        $user->token = $token->plainTextToken;

        // Create refresh token (long-lived: 30 days)
        $user->refresh_token = $user->createRefreshToken();

        return ApiResponse::created(
            new AuthResource($user),
            'users.created'
        );
    }

    /**
     * Login user.
     *
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Authenticate user and get access token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", ref="#/components/schemas/AuthResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user || !\Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
            return ApiResponse::error('auth.invalid_credentials', 401);
        }

        // Create access token (short-lived: 1 hour)
        $token = $user->createToken('auth-token', ['*'], now()->addHour());
        $user->token = $token->plainTextToken;

        // Create refresh token (long-lived: 30 days)
        $user->refresh_token = $user->createRefreshToken();

        // Log login activity
        ActivityLogger::logLogin($user);

        return ApiResponse::success(
            new AuthResource($user),
            'auth.login_success'
        );
    }

    /**
     * Refresh access token.
     *
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh access token",
     *     description="Get a new access token using a valid refresh token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", description="Valid refresh token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired refresh token"
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = $request->user()?->validateRefreshToken($validated['refresh_token']);

        if (!$refreshToken) {
            // Try to find by refresh token from any user
            $hashedToken = hash('sha256', $validated['refresh_token']);
            $refreshToken = \App\Models\RefreshToken::where('hashed_token', $hashedToken)
                ->where('revoked_at', null)
                ->where(function ($query) {
                    $query->where('expires_at', null)
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        if (!$refreshToken || !$refreshToken->isValid()) {
            return ApiResponse::error('auth.invalid_refresh_token', 401);
        }

        $user = $refreshToken->user;

        // Revoke old refresh token
        $refreshToken->revoke();

        // Delete old access tokens
        $user->tokens()->delete();

        // Create new access token (short-lived: 1 hour)
        $token = $user->createToken('auth-token', ['*'], now()->addHour());
        $user->token = $token->plainTextToken;

        // Create new refresh token (long-lived: 30 days)
        $user->refresh_token = $user->createRefreshToken();

        return ApiResponse::success(
            new AuthResource($user),
            'auth.token_refreshed'
        );
    }

    /**
     * Logout user.
     *
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     description="Logout and invalidate current access token",
     *     security={"bearerAuth"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="refresh_token", type="string", description="Optional refresh token to revoke")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('auth.unauthorized', 401);
        }

        // Log logout activity before deleting token (try-catch to prevent 500 error)
        try {
            ActivityLogger::logLogout($user);
        } catch (\Exception $e) {
            // Silently fail logging but continue with logout
        }

        // Revoke refresh token if provided
        $refreshToken = $request->input('refresh_token');
        if ($refreshToken) {
            $token = $user->validateRefreshToken($refreshToken);
            if ($token) {
                $token->revoke();
            }
        }

        // Delete all access tokens
        $user->tokens()->delete();

        // Revoke all refresh tokens
        $user->revokeAllRefreshTokens();

        return ApiResponse::message('auth.logged_out');
    }

    /**
     * Request password reset.
     *
     * @OA\Post(
     *     path="/api/auth/forgot-password",
     *     tags={"Authentication"},
     *     summary="Request password reset",
     *     description="Request a password reset token (simulated)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset token sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reset_token", type="string", example="abc123"),
     *                 @OA\Property(property="note", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user) {
            return ApiResponse::message('auth.password_reset_sent');
        }

        $resetToken = bin2hex(random_bytes(32));

        return ApiResponse::success([
            'reset_token' => $resetToken,
            'note' => 'This is a simulated response. In production, send this token to user email.',
        ], 'auth.password_reset_sent');
    }

    /**
     * Reset password.
     *
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     tags={"Authentication"},
     *     summary="Reset password",
     *     description="Reset password with token (simulated)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","token"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com"),
     *             @OA\Property(property="password", type="string", format="password", example="new password123"),
     *             @OA\Property(property="token", type="string", example="abc123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or email"
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user) {
            return ApiResponse::error('auth.invalid_token', 400);
        }

        $this->authRepository->updatePassword($user, $data['password']);
        $this->authRepository->deleteAllTokens($user);

        // Revoke all refresh tokens
        $user->revokeAllRefreshTokens();

        // Log password reset activity
        ActivityLogger::logPasswordReset($user);

        return ApiResponse::message('auth.password_reset_success');
    }
}

