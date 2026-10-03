<?php

namespace App\Console\Commands;

use App\Support\OperationsHealth;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:operations-check')]
#[Description('Check production configuration and operational dependencies')]
class CheckProductionOperations extends Command
{
    public function handle(): int
    {
        $checks = OperationsHealth::checks();
        $checks = [
            'Production environment' => app()->environment('production'),
            'Debug mode disabled' => ! config('app.debug'),
            'HTTPS application URL' => str_starts_with((string) config('app.url'), 'https://'),
            'Application key configured' => filled(config('app.key')),
            'SMTP mail transport' => ! in_array(config('mail.default'), ['array', 'log'], true),
            'Non-placeholder sender address' => filled(config('mail.from.address')) && ! str_contains((string) config('mail.from.address'), 'example.com'),
            ...collect($checks)->mapWithKeys(fn (bool $passing, string $name) => [str($name)->replace('_', ' ')->title()->toString() => $passing])->all(),
        ];

        $this->table(['Check', 'Result'], collect($checks)->map(fn (bool $passing, string $name) => [$name, $passing ? 'PASS' : 'FAIL'])->values()->all());

        if (in_array(false, $checks, true)) {
            $this->error('Production operations check failed. Correct the failed items before relying on automated notifications.');

            return self::FAILURE;
        }

        $this->info('Production operations check passed.');

        return self::SUCCESS;
    }
}
