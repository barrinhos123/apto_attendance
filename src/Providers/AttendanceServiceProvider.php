<?php

declare(strict_types=1);

namespace Apto\Attendance\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Apto\Attendance\Http\Livewire\AttendanceDashboard;

class AttendanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/attendance.php', 'attendance');
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'attendance');
        $this->registerLivewireComponents();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/attendance.php' => config_path('attendance.php'),
            ], 'attendance-config');
        }
    }

    protected function loadRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('attendance')
            ->name('attendance.')
            ->group(__DIR__ . '/../../routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1/attendance')
            ->name('attendance.api.')
            ->group(__DIR__ . '/../../routes/api.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('external/api/v1/attendance')
            ->name('attendance.external.')
            ->group(__DIR__ . '/../../routes/external.php');
    }

    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('attendance.dashboard', AttendanceDashboard::class);
        }
    }
}
