<?php

declare(strict_types=1);

use App\Support\Concerns\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Build a fresh consumer of the ApiResponse trait. We expose the
 * protected helpers via trait aliases so the assertions can call them
 * without subclassing.
 */
function apiResponseSubject(): object
{
    return new class
    {
        use ApiResponse {
            respondOk as public;
            respondCreated as public;
            respondNoContent as public;
            respondError as public;
        }
    };
}

it('wraps payload in the canonical envelope on respondOk', function (): void {
    $response = apiResponseSubject()->respondOk(['name' => 'Halil']);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and(json_decode($response->getContent() ?: '{}', true))->toBe([
            'status' => 'success',
            'message' => null,
            'data' => ['name' => 'Halil'],
        ]);
});

it('attaches an action message when provided to respondOk', function (): void {
    $response = apiResponseSubject()->respondOk(['ok' => true], message: 'Tamamlandı.');

    expect(json_decode($response->getContent() ?: '{}', true))->toBe([
        'status' => 'success',
        'message' => 'Tamamlandı.',
        'data' => ['ok' => true],
    ]);
});

it('returns 201 from respondCreated and accepts a Location header', function (): void {
    $response = apiResponseSubject()->respondCreated(['id' => 7], '/api/v1/things/7');

    expect($response->getStatusCode())->toBe(201)
        ->and($response->headers->get('Location'))->toBe('/api/v1/things/7');
});

it('emits the canonical error shape from respondError', function (): void {
    $response = apiResponseSubject()->respondError(
        message: 'Invalid credentials',
        code: 'ERR_INVALID_CREDENTIALS',
        status: 401,
    );

    expect($response->getStatusCode())->toBe(401)
        ->and(json_decode($response->getContent() ?: '{}', true))->toBe([
            'status' => 'error',
            'message' => 'Invalid credentials',
            'data' => null,
            'code' => 'ERR_INVALID_CREDENTIALS',
        ]);
});

it('attaches details and validation errors when supplied', function (): void {
    $response = apiResponseSubject()->respondError(
        message: 'Payload invalid',
        code: 'ERR_VALIDATION',
        status: 422,
        details: ['field' => 'email'],
        errors: ['email' => ['The email field is required.']],
    );

    expect(json_decode($response->getContent() ?: '{}', true))->toBe([
        'status' => 'error',
        'message' => 'Payload invalid',
        'data' => null,
        'code' => 'ERR_VALIDATION',
        'details' => ['field' => 'email'],
        'errors' => ['email' => ['The email field is required.']],
    ]);
});

it('returns a 204 from respondNoContent', function (): void {
    $response = apiResponseSubject()->respondNoContent();

    expect($response->getStatusCode())->toBe(204);
});
