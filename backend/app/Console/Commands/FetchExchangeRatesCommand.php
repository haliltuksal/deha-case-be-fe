<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Currency\FetchDailyExchangeRatesAction;
use App\Exceptions\Domain\Currency\ExchangeRateProviderException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class FetchExchangeRatesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'currency:fetch';

    /**
     * @var string
     */
    protected $description = 'Pull the latest exchange rates from the configured upstream provider (TCMB by default).';

    public function handle(FetchDailyExchangeRatesAction $action): int
    {
        try {
            $rates = $action->execute();
        } catch (ExchangeRateProviderException $e) {
            Log::channel('currency')->error('Exchange rate fetch failed at the provider boundary.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            $this->error("Provider failure: {$e->getMessage()}");

            return self::FAILURE;
        } catch (Throwable $e) {
            Log::channel('currency')->error('Exchange rate fetch failed unexpectedly.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            $this->error("Unexpected failure: {$e->getMessage()}");

            return self::FAILURE;
        }

        Log::channel('currency')->info('Exchange rates fetched and persisted.', [
            'count' => $rates->count(),
            'currencies' => $rates->map(fn ($rate): string => $rate->currency->value)->all(),
        ]);

        $this->info(sprintf('Fetched %d exchange rate(s).', $rates->count()));

        foreach ($rates as $rate) {
            $this->line(sprintf(
                '  • %s = %s TRY (fetched %s)',
                $rate->currency->value,
                $rate->rateInTry,
                $rate->fetchedAt->toDateString(),
            ));
        }

        return self::SUCCESS;
    }
}
