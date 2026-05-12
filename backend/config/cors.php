<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The Next.js BFF calls this API server-to-server, so CORS is not on the
| critical path for normal traffic. The configuration below acts as
| defense-in-depth: it explicitly allow-lists the frontend origin(s) so
| any browser-originated request from an unexpected origin is rejected
| at the response-header layer.
|
| FRONTEND_URL accepts a comma-separated list to support local + staging
| + production origins from a single env entry.
|
*/

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('FRONTEND_URL', 'http://localhost:3000')),
)));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Request-Id', 'X-Requested-With'],

    'exposed_headers' => ['X-Request-Id'],

    'max_age' => 600,

    'supports_credentials' => false,

];
