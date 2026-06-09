<?php

use App\Http\Middleware\RequireAdmin;
use App\Http\Middleware\RequiresTwoFactor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'two-factor' => RequiresTwoFactor::class,
            'admin'      => RequireAdmin::class,
        ]);

        $middleware->statefulApi();
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('tasks:send-reminders --days=1')->dailyAt('08:00');
        $schedule->command('tasks:send-reminders --days=3')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
