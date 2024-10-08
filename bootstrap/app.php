<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'isPeyvandtelAdmin' => \App\Http\Middleware\PeyvandtelAdminMiddleware::class,
            'isUser' => \App\Http\Middleware\IsUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
        $schedule->command('services:sahabPartAiSpeechToTextCheckTokens')->everyTwoMinutes();

        $schedule->command('queue:work --max-time=55 --queue=services')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path() . '/logs/queue-jobs-services.log');

        $schedule->command('queue:work --max-time=55 --queue=sms')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path() . '/logs/queue-jobs-sms.log');
    })
    ->create();
