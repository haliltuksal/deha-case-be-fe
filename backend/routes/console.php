<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('currency:fetch')
    ->dailyAt('15:30')
    ->timezone('Europe/Istanbul')
    ->withoutOverlapping()
    ->onFailure(function (): void {
        logger()->channel('stack')->error(
            'Scheduled exchange rate fetch failed; existing cache and database values remain in use.',
        );
    });
