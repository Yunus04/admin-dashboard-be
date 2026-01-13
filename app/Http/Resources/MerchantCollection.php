<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MerchantCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($merchant) {
            return [
                'id' => $merchant->id,
                'user_id' => $merchant->user_id,
                'business_name' => $merchant->business_name,
                'phone' => $merchant->phone,
                'address' => $merchant->address,
                'created_at' => $merchant->created_at,
                'updated_at' => $merchant->updated_at,
                'user' => $merchant->user ? [
                    'id' => $merchant->user->id,
                    'name' => $merchant->user->name,
                    'email' => $merchant->user->email,
                    'role' => $merchant->user->role,
                ] : null,
            ];
        })->toArray();
    }
}
