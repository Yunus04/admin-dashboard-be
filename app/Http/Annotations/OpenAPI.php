<?php

namespace App\Http\Annotations;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Admin Dashboard API",
 *         version="1.0.0",
 *         description="API for Admin Dashboard with Role-Based Access Control",
 *         @OA\Contact(
 *             email="admin@admin.com"
 *         )
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Local Development Server"
 *     ),
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 */
class OpenAPI
{
    // This class is used for OpenAPI annotations
}

