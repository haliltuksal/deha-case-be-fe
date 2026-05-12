<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Concerns\ApiResponse;

/**
 * Base controller for the v1 API surface. Centralises the JSON response
 * helpers so subclasses focus on orchestration only.
 */
abstract class BaseApiController extends Controller
{
    use ApiResponse;
}
