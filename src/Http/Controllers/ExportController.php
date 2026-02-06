<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Apto\Attendance\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __invoke(Request $request, string $format): Response
    {
        $records = $this->recordsForToday($request->user()->id);

        return match ($format) {
            'csv' => $this->asCsv($records),
            'pdf' => $this->pdfPlaceholder(),
            default => response()->noContent(404),
        };
    }

    protected function recordsForToday(int $userId): Collection
    {
        return AttendanceRecord::query()
            ->with('shift')
            ->where('user_id', $userId)
            ->whereDate('clock_in_at', now()->toDateString())
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                'shift' => optional($record->shift)->scheduled_start?->format('H:i') . ' - ' . optional($record->shift)->scheduled_end?->format('H:i'),
                'clock_in' => optional($record->clock_in_at)?->format('H:i'),
                'clock_out' => optional($record->clock_out_at)?->format('H:i'),
                'status' => $record->status,
                'notes' => $record->notes ? strip_tags($record->notes) : null,
                'worked_hours' => $record->clock_in_at && $record->clock_out_at
                    ? $record->clock_in_at->diffInMinutes($record->clock_out_at) / 60
                    : null,
            ]);
    }

    protected function asCsv(Collection $records): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance-summary-' . now()->format('Y-m-d') . '.csv"',
        ];

        $columns = ['Shift', 'Clock in', 'Clock out', 'Status', 'Notes', 'Worked hours'];

        return ResponseFactory::stream(function () use ($records, $columns) {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $columns);

            foreach ($records as $row) {
                fputcsv($handle, [
                    $row['shift'] ?: '—',
                    $row['clock_in'] ?: '—',
                    $row['clock_out'] ?: '—',
                    $row['status'],
                    $row['notes'] ?: '',
                    $row['worked_hours'] ? number_format($row['worked_hours'], 2) : '—',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function pdfPlaceholder(): Response
    {
        return response()->json([
            'message' => 'PDF export is not yet implemented. Integrate a PDF renderer (e.g. barryvdh/laravel-dompdf) to enable this endpoint.',
        ], 501);
    }
}
