<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User Management - Super Admin only"
 * )
 */
class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * List all users.
     *
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get all users",
     *     description="Retrieve a list of all users including deleted ones. Super Admin only. Supports pagination, sorting, and filtering.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (dropdown: 5, 10, 25, 50, 100)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={5,10,25,50,100}, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id","name","email","role","created_at"}, default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/UserResource")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Super Admin only"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $filters = [
            'per_page' => request('per_page'),
            'sort_by' => request('sort_by'),
            'sort_order' => request('sort_order'),
            'search' => request('search'),
            'include_deleted' => filter_var(request('include_deleted'), FILTER_VALIDATE_BOOLEAN),
        ];

        $users = $this->userRepository->getFilteredUsers($filters);

        return response()->json([
            'success' => true,
            'message' => __('messages.users.retrieved'),
            'data' => (new UserCollection($users))->toArray(request()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }

    /**
     * Create a new user.
     *
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Create a new user account. Super Admin only.",
     *     security={"bearerAuth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin","merchant"}, example="merchant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Prevent creating admin users if current user is admin
        $currentUser = $request->user();
        if ($currentUser->isAdmin() && $data['role'] === 'admin') {
            return ApiResponse::error('users.cannot_create_admin', 403);
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
        ]);

        // Log activity
        ActivityLogger::logUserCreated($user, $request->user());

        return ApiResponse::created(
            new UserResource($user),
            'users.created'
        );
    }

    /**
     * Get user details.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get user details",
     *     description="Retrieve user details by ID. Super Admin only.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userRepository->findDeleted($id);

        if (!$user) {
            return ApiResponse::notFound('users.not_found');
        }

        return ApiResponse::success(
            new UserResource(resource: $user),
            'users.retrieved'
        );
    }

    /**
     * Update user.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Update user details. Super Admin only. Cannot change Super Admin role.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", enum={"admin","merchant"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot change Super Admin role"
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $user = $this->userRepository->findDeleted($id);

        if (!$user) {
            return ApiResponse::notFound('users.not_found');
        }

        if ($user->isSuperAdmin() && isset($data['role']) && $data['role'] !== 'super_admin') {
            return ApiResponse::error('users.cannot_change_super_admin', 403);
        }

        // Prevent admin from modifying other admin accounts
        $currentUser = $request->user();
        if ($currentUser->isAdmin() && $user->isAdmin() && $user->id !== $currentUser->id) {
            return ApiResponse::error('users.cannot_manage_admin', 403);
        }

        $updateData = [];
        $oldData = [];

        if (isset($data['name'])) {
            $oldData['name'] = $user->name;
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $oldData['email'] = $user->email;
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        if (isset($data['role'])) {
            $oldData['role'] = $user->role;
            $updateData['role'] = $data['role'];
        }

        $user = $this->userRepository->update($id, data: $updateData);

        // Log activity
        if (!empty($oldData)) {
            ActivityLogger::logUserUpdated($user, $oldData, $updateData, $request->user());
        }

        return ApiResponse::success(
            new UserResource($user),
            'users.updated'
        );
    }

    /**
     * Delete user (soft delete).
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete user",
     *     description="Soft delete a user. Super Admin only. Cannot delete Super Admin.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete Super Admin"
     *     )
     * )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return ApiResponse::notFound('users.not_found');
        }

        if ($user->isSuperAdmin()) {
            return ApiResponse::error('users.cannot_delete_super_admin', 403);
        }

        // Prevent admin from deleting other admin accounts
        $currentUser = $request->user();
        if ($currentUser->isAdmin() && $user->isAdmin() && $user->id !== $currentUser->id) {
            return ApiResponse::error('users.cannot_manage_admin', 403);
        }

        $deletedBy = $request->user();

        $this->userRepository->delete($id);

        // Log activity
        ActivityLogger::logUserDeleted($user, $deletedBy);

        return ApiResponse::message('users.deleted');
    }

    /**
     * Get merchant users for merchant owner selection.
     * Returns only users with 'merchant' role who don't have an active merchant profile yet.
     * Accessible by Super Admin and Admin.
     */
    public function getMerchantUsers(Request $request): JsonResponse
    {
        // Use repository pattern with Eloquent
        $users = $this->userRepository->getMerchantUsers();

        return response()->json([
            'success' => true,
            'message' => __('messages.users.retrieved'),
            'data' => $users,
        ], 200);
    }

    /**
     * Restore deleted user.
     *
     * @OA\Post(
     *     path="/api/users/{id}/restore",
     *     tags={"Users"},
     *     summary="Restore user",
     *     description="Restore a soft-deleted user. Super Admin only.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User restored successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        $restored = $this->userRepository->restore($id);

        if (!$restored) {
            return ApiResponse::notFound('users.not_found');
        }

        $user = $this->userRepository->find($id);

        // Prevent admin from restoring other admin accounts
        $currentUser = $request->user();
        if ($currentUser->isAdmin() && $user->isAdmin() && $user->id !== $currentUser->id) {
            // Rollback the restore
            $this->userRepository->delete($id);
            return ApiResponse::error('users.cannot_manage_admin', 403);
        }

        // Log activity
        ActivityLogger::logUserRestored($user, $request->user());

        return ApiResponse::success(
            new UserResource($user),
            'users.restored'
        );
    }
}

