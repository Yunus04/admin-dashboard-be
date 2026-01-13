<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    /**
     * Translate message key with fallback
     */
    private static function translate(string $key): string
    {
        // Check if key already has prefix
        if (str_starts_with($key, 'messages.')) {
            return __($key);
        }

        // Try to translate with messages prefix
        $translated = __('messages.' . $key);

        // If translation is same as key (not found), try without prefix
        if ($translated === 'messages.' . $key) {
            $translated = __($key);
        }

        return $translated;
    }

    /**
     * Summary of success
     * @param mixed $data
     * @param string $key
     * @param int $status
     * @param array $additional Additional keys to include in response
     * @return JsonResponse
     */
    public static function success(mixed $data = null, string $key = 'general.success', int $status = 200, array $additional = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => self::translate($key),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Add any additional keys
        foreach ($additional as $key => $value) {
            $response[$key] = $value;
        }

        return response()->json($response, $status);
    }

    /**
     * Summary of message
     * @param string $key
     * @param int $status
     * @return JsonResponse
     */
    public static function message(string $key = 'general.success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => self::translate($key),
            'data' => null,
        ], $status);
    }

    /**
     * Summary of error
     * @param string $key
     * @param int $status
     * @return JsonResponse
     */
    public static function error(string $key = 'general.error', int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => self::translate($key),
        ], $status);
    }

    /**
     * Summary of validationError
     * @param array $errors
     * @param string $key
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $key = 'general.validation_failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => self::translate($key),
            'errors' => $errors,
        ], 422);
    }

    /**
     * Summary of errors
     * @param array $errors
     * @param int $status
     * @return JsonResponse
     */
    public static function errors(array $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Created response.
     * @param mixed $data
     * @param string $key
     * @return JsonResponse
     */
    public static function created(mixed $data = null, string $key = 'general.created'): JsonResponse
    {
        return self::success($data, $key, 201);
    }

    /**
     * Not found response.
     * @param string $key
     * @return JsonResponse
     */
    public static function notFound(string $key = 'general.not_found'): JsonResponse
    {
        return self::error($key, 404);
    }

    /**
     * Unauthorized response.
     * @param string $key
     * @return JsonResponse
     */
    public static function unauthorized(string $key = 'general.unauthorized'): JsonResponse
    {
        return self::error($key, 401);
    }

    /**
     * Forbidden response.
     * @param string $key
     * @return JsonResponse
     */
    public static function forbidden(string $key = 'general.forbidden'): JsonResponse
    {
        return self::error($key, 403);
    }

    /**
     * Summary of resource
     * @param JsonResource $resource
     * @param string $message
     * @return JsonResponse
     */
    public static function resource(JsonResource $resource, string $key = 'general.success'): JsonResponse
    {
        return $resource->additional([
            'success' => true,
            'message' => self::translate($key),
        ])->response();
    }

    /**
     * Summary of collection
     * @param ResourceCollection $collection
     * @param string $message
     * @return JsonResponse
     */
    public static function collection(ResourceCollection $collection, string $key = 'general.success'): JsonResponse
    {
        return $collection->additional([
            'success' => true,
            'message' => self::translate($key),
        ])->response();
    }

    /**
     * Format paginated collection.
     * @param ResourceCollection $collection
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated(ResourceCollection $collection, string $key = 'general.success'): JsonResponse
    {
        return $collection->additional([
            'success' => true,
            'message' => self::translate($key),
        ])->response();
    }
}

