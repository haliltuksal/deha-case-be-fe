<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class RefreshTokenAction
{
    public function __construct(
        private JWTGuard $guard,
    ) {}

    /**
     * Rotate the current token: blacklist the previous one and emit a fresh
     * token with the same identity. Token expiry/grace behaviour is governed
     * by the package's `blacklist_grace_period` setting.
     */
    public function execute(): string
    {
        return $this->guard->refresh();
    }
}
