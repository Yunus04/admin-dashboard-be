<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\MerchantCollection;
use App\Http\Resources\MerchantResource;
use App\Repositories\Interfaces\MerchantRepositoryInterface;
use App\Http\Requests\Merchant\StoreMerchantRequest;
use App\Http\Requests\Merchant\UpdateMerchantRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

/**
 * @OA\Tag(
 *     name="Merchants",
 *     description="Merchant Management"
 * )
 */
class MerchantController extends Controller
{
    protected MerchantRepositoryInterface $merchantRepository;

    public function __construct(MerchantRepositoryInterface $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

    /**
     * List all merchants.
     *
     * @OA\Get(
     *     path="/api/merchants",
     *     tags={"Merchants"},
     *     summary="Get all merchants",
     *     description="Retrieve merchants based on user role. Super Admin & Admin: all merchants. Merchant: only own profile. Supports pagination, sorting, and filtering.",
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
     *         @OA\Schema(type="string", enum={"id","user_id","business_name","phone","created_at"}, default="created_at")
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
     *         description="Search in business_name or address",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Merchants retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Merchants retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MerchantResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = [
            'per_page' => request('per_page'),
            'sort_by' => request('sort_by'),
            'sort_order' => request('sort_order'),
            'search' => request('search'),
            'user_id' => $user->isMerchant() ? $user->id : null,
        ];

        $merchants = $this->merchantRepository->getFilteredMerchants($filters);

        return ApiResponse::success(
            (new MerchantCollection($merchants))->toArray(request()),
            'merchants.retrieved',
            200,
            [
                'current_page' => $merchants->currentPage(),
                'last_page' => $merchants->lastPage(),
                'per_page' => $merchants->perPage(),
                'total' => $merchants->total(),
            ]
        );
    }

    /**
     * Create a new merchant.
     *
     * @OA\Post(
     *     path="/api/merchants",
     *     tags={"Merchants"},
     *     summary="Create a new merchant",
     *     description="Create a new merchant profile. Super Admin & Admin only.",
     *     security={"bearerAuth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","business_name"},
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="business_name", type="string", example="My Store"),
     *             @OA\Property(property="phone", type="string", example="081234567890"),
     *             @OA\Property(property="address", type="string", example="New York, USA")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Merchant created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MerchantResource")
     *     ),
     *     @OA\Response(response=400, description="User already has a merchant profile"),
     *     @OA\Response(response=403, description="Forbidden - Merchants cannot create")
     *     )
     */
    public function store(StoreMerchantRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if user already has a merchant profile
        $existingMerchant = $this->merchantRepository->findByUserId($data['user_id']);
        if ($existingMerchant) {
            return ApiResponse::error('merchants.already_exists', 400);
        }

        $merchant = $this->merchantRepository->create([
            'user_id' => $data['user_id'],
            'business_name' => $data['business_name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $merchant->load('user');

        // Log activity
        ActivityLogger::logMerchantCreated($merchant, $request->user());

        return ApiResponse::created(
            new MerchantResource($merchant),
            'merchants.created'
        );
    }

    /**
     * Get merchant details.
     *
     * @OA\Get(
     *     path="/api/merchants/{id}",
     *     tags={"Merchants"},
     *     summary="Get merchant details",
     *     description="Retrieve merchant details by ID. Merchant can only view own profile.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Merchant retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MerchantResource")
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Not your profile"),
     *     @OA\Response(response=404, description="Merchant not found")
     *     )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $merchant = $this->merchantRepository->findWithUser($id);

        // Check if merchant exists
        if (!$merchant) {
            return ApiResponse::notFound('merchants.not_found');
        }

        // Check if merchant can view this profile
        if ($user->isMerchant() && $merchant->user_id !== $user->id) {
            return ApiResponse::error('merchants.forbidden_view', 403);
        }

        return ApiResponse::success(
            new MerchantResource($merchant),
            'merchants.retrieved'
        );
    }

    /**
     * Update merchant.
     *
     * @OA\Put(
     *     path="/api/merchants/{id}",
     *     tags={"Merchants"},
     *     summary="Update merchant",
     *     description="Update merchant details. Merchant can only update own profile.",
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
     *             @OA\Property(property="business_name", type="string", example="New Store"),
     *             @OA\Property(property="phone", type="string", example="081234567890"),
     *             @OA\Property(property="address", type="string", example="Chicago, USA")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Merchant updated successfully"),
     *     @OA\Response(response=403, description="Forbidden - Not your profile"),
     *     @OA\Response(response=404, description="Merchant not found")
     *     )
     */
    public function update(Request $request, UpdateMerchantRequest $requestObj, string $id): JsonResponse
    {
        $user = $request->user();
        $merchant = $this->merchantRepository->find($id);

        // Check if merchant exists
        if (!$merchant) {
            return ApiResponse::notFound('merchants.not_found');
        }

        // Check if merchant can update this profile
        if ($user->isMerchant() && $merchant->user_id !== $user->id) {
            return ApiResponse::error('merchants.forbidden_update', 403);
        }

        $data = $requestObj->validated();
        $oldData = [];

        if (isset($data['business_name'])) {
            $oldData['business_name'] = $merchant->business_name;
        }
        if (isset($data['phone'])) {
            $oldData['phone'] = $merchant->phone;
        }
        if (isset($data['address'])) {
            $oldData['address'] = $merchant->address;
        }

        $this->merchantRepository->update($id, $data);
        $merchant = $this->merchantRepository->find($id);

        // Log activity
        if (!empty($oldData)) {
            ActivityLogger::logMerchantUpdated($merchant, $oldData, $data, $user);
        }

        return ApiResponse::success(
            new MerchantResource($merchant),
            'merchants.updated'
        );
    }

    /**
     * Delete merchant (soft delete).
     *
     * @OA\Delete(
     *     path="/api/merchants/{id}",
     *     tags={"Merchants"},
     *     summary="Delete merchant",
     *     description="Soft delete a merchant. Super Admin & Admin only.",
     *     security={"bearerAuth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Merchant deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden - Merchants cannot delete"),
     *     @OA\Response(response=404, description="Merchant not found")
     *     )
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        // Check if merchant can be deleted
        if ($user->isMerchant()) {
            return ApiResponse::error('merchants.forbidden_delete', 403);
        }

        // Check if merchant exists
        $merchant = $this->merchantRepository->find($id);
        if (!$merchant) {
            return ApiResponse::notFound('merchants.not_found');
        }

        // Proceed with deletion and log activity
        $this->merchantRepository->delete($id);
        ActivityLogger::logMerchantDeleted($merchant, $user);

        return ApiResponse::message('merchants.deleted');
    }
}

