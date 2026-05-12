<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class LogoutUserAction
{
    public function __construct(
        private JWTGuard $guard,
    ) {}

    /**
     * Invalidate the current bearer token. The package pushes the token's
     * jti onto the configured blacklist so subsequent requests reusing the
     * same token are rejected.
     */
    public function execute(): void
    {
        $this->guard->logout();
    }
}
