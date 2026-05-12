<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared base for every API form request. Subclasses declare `rules()` and,
 * where relevant, override `authorize()` and the DTO mapping.
 *
 * Concrete requests are expected to expose a static `toDto(self $request): mixed`
 * helper or an instance `toDto()` method, populated from `validated()`.
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Authorize all requests by default. Resource ownership is enforced at
     * the action / policy layer rather than here; this keeps the form
     * request focused on input validation.
     */
    public function authorize(): bool
    {
        return true;
    }
}
