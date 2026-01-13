<?php

namespace Tests\Unit;

use App\Helpers\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response(): void
    {
        $response = ApiResponse::success(['key' => 'value'], 'Operation successful');

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertTrue($content['success']);
        $this->assertEquals('Operation successful', $content['message']);
        $this->assertEquals(['key' => 'value'], $content['data']);
    }

    public function test_created_response(): void
    {
        $response = ApiResponse::created(['id' => 1], 'Resource created');

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertTrue($content['success']);
        $this->assertEquals('Resource created', $content['message']);
    }

    public function test_error_response(): void
    {
        $response = ApiResponse::error('Something went wrong', 400);

        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['success']);
        $this->assertEquals('Something went wrong', $content['message']);
    }

    public function test_not_found_response(): void
    {
        $response = ApiResponse::notFound('general.not_found');

        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['success']);
        $this->assertEquals('general.not_found', $content['message']);
    }

    public function test_unauthorized_response(): void
    {
        $response = ApiResponse::unauthorized();

        $this->assertEquals(401, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['success']);
        $this->assertEquals('general.unauthorized', $content['message']);
    }

    public function test_forbidden_response(): void
    {
        $response = ApiResponse::forbidden();

        $this->assertEquals(403, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['success']);
        $this->assertEquals('general.forbidden', $content['message']);
    }

    public function test_validation_error_response(): void
    {
        $errors = ['email' => ['The email field is required.']];
        $response = ApiResponse::validationError($errors);

        $this->assertEquals(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['success']);
        $this->assertEquals('general.validation_failed', $content['message']);
        $this->assertArrayHasKey('errors', $content);
    }

    public function test_message_response(): void
    {
        $response = ApiResponse::message('Custom message', 200);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertTrue($content['success']);
        $this->assertEquals('Custom message', $content['message']);
        $this->assertNull($content['data']);
    }
}

