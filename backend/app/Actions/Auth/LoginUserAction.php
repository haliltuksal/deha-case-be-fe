<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginCredentialsData;
use App\Exceptions\Domain\Auth\InvalidCredentialsException;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class LoginUserAction
{
    public function __construct(
        private JWTGuard $guard,
    ) {}

    /**
     * Verify the credentials and return a freshly issued access token.
     *
     * @throws InvalidCredentialsException when no user matches the provided email/password pair
     */
    public function execute(LoginCredentialsData $credentials): string
    {
        $token = $this->guard->attempt($credentials->toCredentialsArray());

        if (! is_string($token) || $token === '') {
            Log::channel('auth')->warning('Login attempt rejected', [
                'email' => $credentials->email,
            ]);

            throw new InvalidCredentialsException;
        }

        Log::channel('auth')->info('Login attempt accepted', [
            'email' => $credentials->email,
        ]);

        return $token;
    }
}
