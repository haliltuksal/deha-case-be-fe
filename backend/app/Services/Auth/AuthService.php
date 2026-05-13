<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Actions\Auth\RefreshTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\AuthenticatedUser;
use App\DTOs\Auth\LoginCredentialsData;
use App\DTOs\Auth\RegisterUserData;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class AuthService
{
    public function __construct(
        private RegisterUserAction $registerAction,
        private LoginUserAction $loginAction,
        private LogoutUserAction $logoutAction,
        private RefreshTokenAction $refreshAction,
        private JWTGuard $guard,
    ) {}

    public function register(RegisterUserData $data): AuthenticatedUser
    {
        $user = $this->registerAction->execute($data);
        $token = $this->guard->login($user);

        return $this->buildAuthenticatedUser($user, $token);
    }

    public function login(LoginCredentialsData $credentials): AuthenticatedUser
    {
        $token = $this->loginAction->execute($credentials);
        /** @var User $user */
        $user = $this->guard->user();

        return $this->buildAuthenticatedUser($user, $token);
    }

    public function logout(): void
    {
        $this->logoutAction->execute();
    }

    public function refresh(): AuthenticatedUser
    {
        $token = $this->refreshAction->execute();
        /** @var User $user */
        $user = $this->guard->setToken($token)->user();

        return $this->buildAuthenticatedUser($user, $token);
    }

    public function currentUser(): User
    {
        /** @var User $user */
        $user = $this->guard->user();

        return $user;
    }

    private function buildAuthenticatedUser(User $user, string $token): AuthenticatedUser
    {
        return new AuthenticatedUser(
            user: $user,
            token: $token,
            expiresIn: $this->guard->factory()->getTTL() * 60,
        );
    }
}
