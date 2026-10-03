<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class OperationsHealth
{
    /** @return array<string, bool> */
    public static function checks(): array
    {
        try {
            DB::select('select 1');
            $database = true;
        } catch (Throwable) {
            $database = false;
        }

        try {
            $heartbeat = Cache::get('operations.scheduler_last_run');
            $scheduler = $heartbeat && Carbon::parse($heartbeat)->greaterThan(now()->subMinutes(5));
            $cache = true;
        } catch (Throwable) {
            $scheduler = false;
            $cache = false;
        }

        return [
            'database' => $database,
            'cache' => $cache,
            'storage' => is_writable(storage_path('framework')) && is_writable(storage_path('logs')),
            'public_storage' => is_dir(public_path('storage')),
            'queue' => config('queue.default') === 'database',
            'scheduler' => (bool) $scheduler,
        ];
    }

    public static function healthy(array $checks): bool
    {
        return ! in_array(false, $checks, true);
    }
}
