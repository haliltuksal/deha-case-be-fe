<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final readonly class LogoutUserAction
{
    public function __construct(
        private JWTGuard $guard,
    ) {}

    public function execute(): void
    {
        $this->guard->logout();
    }
}
