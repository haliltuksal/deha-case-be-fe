<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Concerns\ApiResponse;

abstract class BaseApiController extends Controller
{
    use ApiResponse;
}
