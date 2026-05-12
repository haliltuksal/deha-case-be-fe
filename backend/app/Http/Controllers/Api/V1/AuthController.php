<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Auth\LoginCredentialsData;
use App\DTOs\Auth\RegisterUserData;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Http\Resources\V1\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    /**
     * Register
     *
     * Create a new user account and immediately issue a JWT.
     *
     * @group Auth
     *
     * @unauthenticated
     *
     * @bodyParam name string required Display name (2–255 chars). Example: Halil Tuksal
     * @bodyParam email string required Unique RFC-valid email address. Example: halil@example.com
     * @bodyParam password string required Minimum 8 characters with at least one letter and one number. Example: secret123
     * @bodyParam password_confirmation string required Must match the password field. Example: secret123
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Hesap oluşturuldu.",
     *   "data": {
     *     "access_token": "eyJ0eXAiOi...",
     *     "token_type": "bearer",
     *     "expires_in": 3600,
     *     "user": {
     *       "id": 7,
     *       "name": "Halil Tuksal",
     *       "email": "halil@example.com",
     *       "is_admin": false,
     *       "created_at": "2026-05-04T20:00:00+00:00"
     *     }
     *   }
     * }
     * @response 422 scenario="email already taken" {
     *   "status": "error",
     *   "message": "Bu e-posta adresi zaten kayıtlı.",
     *   "data": null,
     *   "code": "ERR_VALIDATION",
     *   "errors": {"email": ["Bu e-posta adresi zaten kayıtlı."]}
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $authenticated = $this->auth->register(RegisterUserData::fromRequest($request));

        return $this->respondCreated(
            AuthTokenResource::make($authenticated),
            message: 'Hesap oluşturuldu.',
        );
    }

    /**
     * Login
     *
     * Verify credentials and return a fresh JWT.
     *
     * @group Auth
     *
     * @unauthenticated
     *
     * @bodyParam email string required Account email. Example: customer@dehasoft.test
     * @bodyParam password string required Account password. Example: password
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Giriş başarılı.",
     *   "data": {
     *     "access_token": "eyJ0eXAiOi...",
     *     "token_type": "bearer",
     *     "expires_in": 3600,
     *     "user": {
     *       "id": 2,
     *       "name": "Demo Customer",
     *       "email": "customer@dehasoft.test",
     *       "is_admin": false,
     *       "created_at": "2026-05-04T18:00:00+00:00"
     *     }
     *   }
     * }
     * @response 401 scenario="bad credentials" {
     *   "status": "error",
     *   "message": "The provided credentials are invalid.",
     *   "data": null,
     *   "code": "ERR_INVALID_CREDENTIALS"
     * }
     * @response 429 scenario="rate limit exceeded" {
     *   "status": "error",
     *   "message": "Too many requests. Please slow down.",
     *   "data": null,
     *   "code": "ERR_TOO_MANY_REQUESTS"
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $authenticated = $this->auth->login(LoginCredentialsData::fromRequest($request));

        return $this->respondOk(AuthTokenResource::make($authenticated), message: 'Giriş başarılı.');
    }

    /**
     * Logout
     *
     * Blacklist the current bearer token so it cannot be reused.
     *
     * @group Auth
     *
     * @authenticated
     *
     * @response 204 {}
     */
    public function logout(): Response
    {
        $this->auth->logout();

        return $this->respondNoContent();
    }

    /**
     * Refresh token
     *
     * Rotate the current bearer token. The previous token is blacklisted
     * and a brand-new one is returned alongside the user payload.
     *
     * @group Auth
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Oturum yenilendi.",
     *   "data": {
     *     "access_token": "eyJ0eXAiOi...newer...",
     *     "token_type": "bearer",
     *     "expires_in": 3600,
     *     "user": {"id": 2, "name": "Demo Customer", "email": "customer@dehasoft.test", "is_admin": false, "created_at": "2026-05-04T18:00:00+00:00"}
     *   }
     * }
     */
    public function refresh(): JsonResponse
    {
        return $this->respondOk(AuthTokenResource::make($this->auth->refresh()), message: 'Oturum yenilendi.');
    }

    /**
     * Current user
     *
     * Return the user identified by the bearer token.
     *
     * @group Auth
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "id": 2,
     *     "name": "Demo Customer",
     *     "email": "customer@dehasoft.test",
     *     "is_admin": false,
     *     "created_at": "2026-05-04T18:00:00+00:00"
     *   }
     * }
     */
    public function me(): JsonResponse
    {
        return $this->respondOk(UserResource::make($this->auth->currentUser()));
    }
}
