<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Auth;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class InvalidCredentialsException extends ApiException
{
    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_UNAUTHORIZED;
    }

    public function getErrorCode(): string
    {
        return 'ERR_INVALID_CREDENTIALS';
    }

    protected function defaultMessage(): string
    {
        return 'The provided credentials are invalid.';
    }
}
