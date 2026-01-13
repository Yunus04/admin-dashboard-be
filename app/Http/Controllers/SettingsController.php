<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Settings",
 *     description="User Settings - Profile, Password"
 * )
 */
class SettingsController extends Controller
{
    /**
     * Get user profile.
     *
     * @OA\Get(
     *     path="/api/settings/profile",
     *     tags={"Settings"},
     *     summary="Get profile",
     *     description="Get the authenticated user's profile information.",
     *     security={"bearerAuth"},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully"
     *     )
     * )
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success(
            new UserResource($user),
            'settings.profile_retrieved'
        );
    }

    /**
     * Update user profile.
     *
     * @OA\Patch(
     *     path="/api/settings/profile",
     *     tags={"Settings"},
     *     summary="Update profile",
     *     description="Update the authenticated user's profile information.",
     *     security={"bearerAuth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|min:2|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $user->update($validated);

        return ApiResponse::success(
            new UserResource($user),
            'settings.profile_updated'
        );
    }

    /**
     * Change password.
     *
     * @OA\Post(
     *     path="/api/settings/change-password",
     *     tags={"Settings"},
     *     summary="Change password",
     *     description="Change the authenticated user's password.",
     *     security={"bearerAuth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password"},
     *             @OA\Property(property="current_password", type="string", format="password"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('settings.current_password_wrong', 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Revoke all other tokens for security
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return ApiResponse::message('settings.password_changed');
    }
}

