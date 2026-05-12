<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\DTOs\Auth\AuthenticatedUser;
use Illuminate\Http\Request;

/**
 * @property AuthenticatedUser $resource
 */
final class AuthTokenResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource->token,
            'token_type' => 'bearer',
            'expires_in' => $this->resource->expiresIn,
            'user' => UserResource::make($this->resource->user),
        ];
    }
}
