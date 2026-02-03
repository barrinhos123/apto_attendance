<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Apto\Attendance\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = AttendanceRecord::query()
            ->with(['user', 'shift'])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->latest('clock_in_at')
            ->paginate(25);

        return response()->json($records);
    }

    public function show(int $record, Request $request): JsonResponse
    {
        $attendance = AttendanceRecord::query()
            ->with(['user', 'shift', 'events'])
            ->findOrFail($record);

        return response()->json([
            'data' => $attendance,
        ]);
    }
}
