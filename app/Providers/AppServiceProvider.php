<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        $queries = 0;
        $time = 0;
        $slowQueries = [];

        DB::listen(function ($query) use (&$queries, &$time, &$slowQueries) {
            $queries++;
            $time += $query->time;

            if ($query->time >= 100) {
                $slowQueries[] = [
                    'time_ms' => $query->time,
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                ];
            }
        });

        app()->terminating(function () use (&$queries, &$time, &$slowQueries) {
            Log::info('SQL SUMMARY', [
                'queries' => $queries,
                'time_ms' => round($time, 2),
                'slow_queries' => $slowQueries,
            ]);
        });
    }
}
