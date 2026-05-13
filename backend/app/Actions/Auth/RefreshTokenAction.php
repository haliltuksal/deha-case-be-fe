<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class RefreshTokenAction
{
    public function __construct(
        private JWTGuard $guard,
    ) {}

    public function execute(): string
    {
        return $this->guard->refresh();
    }
}
