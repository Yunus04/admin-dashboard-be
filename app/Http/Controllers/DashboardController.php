<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\MerchantRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard Data - Role-based"
 * )
 */
class DashboardController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected MerchantRepositoryInterface $merchantRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->userRepository = $userRepository;
        $this->merchantRepository = $merchantRepository;
    }

    /**
     * Get dashboard data based on user role.
     *
     * @OA\Get(
     *     path="/api/dashboard",
     *     tags={"Dashboard"},
     *     summary="Get dashboard data",
     *     description="Retrieve dashboard data based on user role. Super Admin: all data. Admin: merchant data only. Merchant: own profile.",
     *     security={"bearerAuth"},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard data retrieved successfully",
     *         @OA\JsonContent(
     *             oneOf=[
     *                 @OA\Schema(ref="#/components/schemas/SuperAdminDashboard"),
     *                 @OA\Schema(ref="#/components/schemas/AdminDashboard"),
     *                 @OA\Schema(ref="#/components/schemas/MerchantDashboard")
     *             ]
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Determine dashboard data based on user role and return immediately
        return match (true) {
            $user->isSuperAdmin() => $this->superAdminDashboard(),
            $user->isAdmin() => $this->adminDashboard(),
            $user->isMerchant() => $this->merchantDashboard($request),
            default => ApiResponse::error('general.invalid_role', 400),
        };
    }

    /**
     * Super Admin Dashboard.
     *
     * @OA\Schema(
     *     schema="SuperAdminDashboard",
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Super Admin Dashboard data"),
     *     @OA\Property(property="data", type="object",
     *         @OA\Property(property="summary", type="object",
     *             @OA\Property(property="total_users", type="integer", example=10),
     *             @OA\Property(property="total_merchants", type="integer", example=5),
     *             @OA\Property(property="active_merchants", type="integer", example=4)
     *         ),
     *         @OA\Property(property="users_by_role", type="object"),
     *         @OA\Property(property="recent_users", type="array", @OA\Items()),
     *         @OA\Property(property="recent_merchants", type="array", @OA\Items())
     *     )
     * )
     */
    private function superAdminDashboard(): JsonResponse
    {
        $totalUsers = $this->userRepository->all()->count();
        $totalMerchants = $this->merchantRepository->all()->count();
        $activeMerchants = $this->merchantRepository->getWithActiveUser()->count();
        $usersByRole = $this->userRepository->getUsersByRoleCount();
        $recentUsers = $this->userRepository->getRecentUsers(5);
        $recentMerchants = $this->merchantRepository->getRecentWithUser(5);

        return ApiResponse::success([
            'summary' => [
                'total_users' => $totalUsers,
                'total_merchants' => $totalMerchants,
                'active_merchants' => $activeMerchants,
            ],
            'users_by_role' => $usersByRole,
            'recent_users' => $recentUsers,
            'recent_merchants' => $recentMerchants,
        ], 'dashboard.super_admin');
    }

    /**
     * Admin Dashboard.
     *
     * @OA\Schema(
     *     schema="AdminDashboard",
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Admin Dashboard data"),
     *     @OA\Property(property="data", type="object",
     *         @OA\Property(property="summary", type="object",
     *             @OA\Property(property="total_merchants", type="integer", example=5),
     *             @OA\Property(property="active_merchants", type="integer", example=4),
     *             @OA\Property(property="inactive_merchants", type="integer", example=1)
     *         ),
     *         @OA\Property(property="recent_merchants", type="array", @OA\Items())
     *     )
     * )
     */
    private function adminDashboard(): JsonResponse
    {
        $totalMerchants = $this->merchantRepository->all()->count();
        $activeMerchants = $this->merchantRepository->getWithActiveUser()->count();
        $inactiveMerchants = $this->merchantRepository->countInactive();
        $recentMerchants = $this->merchantRepository->getRecentWithUser(5);

        return ApiResponse::success([
            'summary' => [
                'total_merchants' => $totalMerchants,
                'active_merchants' => $activeMerchants,
                'inactive_merchants' => $inactiveMerchants,
            ],
            'recent_merchants' => $recentMerchants,
        ], 'dashboard.admin');
    }

    /**
     * Merchant Dashboard.
     *
     *     @OA\Schema(
     *     schema="MerchantDashboard",
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Merchant Dashboard data"),
     *     @OA\Property(property="data", type="object",
     *         @OA\Property(property="merchant", type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="business_name", type="string", example="My Store"),
     *             @OA\Property(property="phone", type="string", example="081234567890"),
     *             @OA\Property(property="address", type="string", example="New York"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             )
     *         )
     *     )
     * )
     */
    private function merchantDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $merchant = $this->merchantRepository->findByUserId($user->id);

        // Prepare merchant data or null if not found
        $merchantData = null;

        if ($merchant) {
            $merchantData = [
                'id' => $merchant->id,
                'business_name' => $merchant->business_name,
                'phone' => $merchant->phone,
                'address' => $merchant->address,
                'user' => [
                    'id' => $merchant->user->id,
                    'name' => $merchant->user->name,
                    'email' => $merchant->user->email,
                ],
                'created_at' => $merchant->created_at,
            ];
        }

        return ApiResponse::success([
            'merchant' => $merchantData,
        ], $merchantData ? 'dashboard.merchant' : 'dashboard.merchant_not_found');
    }
}

