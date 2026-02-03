<?php

use Illuminate\Support\Facades\Route;
use Apto\Attendance\Http\Controllers\Api\AttendanceActionController;
use Apto\Attendance\Http\Controllers\Api\AttendanceRecordController;

Route::get('/records', [AttendanceRecordController::class, 'index'])->name('records.index');
Route::get('/records/{record}', [AttendanceRecordController::class, 'show'])->name('records.show');

Route::post('/clock-in', [AttendanceActionController::class, 'clockIn'])->name('clock-in');
Route::post('/clock-out', [AttendanceActionController::class, 'clockOut'])->name('clock-out');
Route::post('/break/start', [AttendanceActionController::class, 'breakStart'])->name('break-start');
Route::post('/break/end', [AttendanceActionController::class, 'breakEnd'])->name('break-end');
Route::post('/absence', [AttendanceActionController::class, 'registerAbsence'])->name('absence');
