<?php

declare(strict_types=1);

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| All Feature tests extend our base TestCase. Tests that mutate the
| database opt into RefreshDatabase explicitly via `uses(...)` at the top
| of the file, so suites that don't need a fresh schema don't pay for one.
|
*/

pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));
