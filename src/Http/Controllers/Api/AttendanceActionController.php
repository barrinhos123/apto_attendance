<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Apto\Attendance\Services\AttendanceRecorder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AttendanceActionController extends Controller
{
    public function __construct(private readonly AttendanceRecorder $recorder)
    {
    }

    public function clockIn(Request $request): JsonResponse
    {
        $scheduleId = $request->filled('schedule_id') ? $request->integer('schedule_id') : null;
        $latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;

        $record = $this->recorder->clockIn(
            $request->user(),
            $scheduleId,
            $request->string('notes')->toString() ?: null,
            $latitude,
            $longitude
        );

        return response()->json([
            'data' => $record->fresh(['user', 'shift', 'schedule']),
        ]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        try {
            $record = $this->recorder->clockOut(
                $request->user(),
                $request->string('notes')->toString() ?: null
            );
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => __('No active attendance record found.'),
            ], 404);
        }

        return response()->json([
            'data' => $record->fresh(['user', 'shift', 'schedule']),
        ]);
    }

    public function breakStart(Request $request): JsonResponse
    {
        try {
            $record = $this->recorder->startBreak(
                $request->user(),
                $request->string('notes')->toString() ?: null
            );
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => __('No active attendance record found.'),
            ], 404);
        }

        return response()->json([
            'data' => $record->fresh(['user']),
        ]);
    }

    public function breakEnd(Request $request): JsonResponse
    {
        try {
            $record = $this->recorder->endBreak(
                $request->user(),
                $request->string('notes')->toString() ?: null
            );
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => __('No active attendance record found.'),
            ], 404);
        }

        return response()->json([
            'data' => $record->fresh(['user']),
        ]);
    }

    public function registerAbsence(Request $request): JsonResponse
    {
        $record = $this->recorder->registerAbsence(
            $request->user(),
            $request->integer('shift_id'),
            $request->string('notes')->toString() ?: null,
        );

        return response()->json([
            'data' => $record->fresh(['user']),
        ], 201);
    }
}
