<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \Apto\Attendance\Http\Livewire\AttendanceDashboard::class)
    ->name('dashboard');

Route::get('/exports/{format}', \Apto\Attendance\Http\Controllers\ExportController::class)
    ->whereIn('format', ['csv', 'pdf'])
    ->name('exports.download');

Route::get('/schedules', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'index'])
    ->name('schedules.index');
Route::get('/schedules/create', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'create'])
    ->name('schedules.create');
Route::post('/schedules', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'store'])
    ->name('schedules.store');
Route::get('/schedules/{schedule}', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'edit'])
    ->name('schedules.edit')
    ->where('schedule', '[0-9]+');
Route::put('/schedules/{schedule}', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'update'])
    ->name('schedules.update')
    ->where('schedule', '[0-9]+');
Route::delete('/schedules/{schedule}', [\Apto\Attendance\Http\Controllers\ScheduleController::class, 'destroy'])
    ->name('schedules.destroy')
    ->where('schedule', '[0-9]+');
