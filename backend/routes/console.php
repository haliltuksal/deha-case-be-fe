<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// TCMB publishes its indicative rates ("Indicative Exchange Rates Announced
// at 15:30") at 15:30 Europe/Istanbul. Fire the fetch right at announcement
// time; `withoutOverlapping()` prevents a slow upstream from racing the
// next day's run.
Schedule::command('currency:fetch')
    ->dailyAt('15:30')
    ->timezone('Europe/Istanbul')
    ->withoutOverlapping()
    ->onFailure(function (): void {
        logger()->channel('stack')->error(
            'Scheduled exchange rate fetch failed; existing cache and database values remain in use.',
        );
    });
