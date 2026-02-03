<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \Apto\Attendance\Http\Livewire\AttendanceDashboard::class)
    ->name('dashboard');

Route::get('/exports/{format}', \Apto\Attendance\Http\Controllers\ExportController::class)
    ->whereIn('format', ['csv', 'pdf'])
    ->name('exports.download');
