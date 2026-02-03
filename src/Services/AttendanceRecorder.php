<?php

declare(strict_types=1);

namespace Apto\Attendance\Services;

use App\Models\User;
use Apto\Attendance\Events\AttendanceRecorded;
use Apto\Attendance\Models\AttendanceEvent;
use Apto\Attendance\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AttendanceRecorder
{
    public function clockIn(User $user, ?int $shiftId = null, ?string $notes = null, ?string $location = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $shiftId, $notes, $location) {
            $record = AttendanceRecord::create([
                'user_id'     => $user->id,
                'shift_id'    => $shiftId,
                'clock_in_at' => now(),
                'status'      => 'in_progress',
                'notes'       => $notes,
                'location'    => $location,
            ]);

            $this->recordEvent($record, $user, 'clock_in', $notes, $location);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record;
        });
    }

    public function clockOut(User $user, ?string $notes = null, ?string $location = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $location) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'clock_out_at' => now(),
                'status'       => 'completed',
                'notes'        => $notes ?? $record->notes,
                'location'     => $location ?? $record->location,
            ]);

            $this->recordEvent($record, $user, 'clock_out', $notes, $location);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record->fresh();
        });
    }

    public function startBreak(User $user, ?string $notes = null, ?string $location = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $location) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'status' => 'on_break',
            ]);

            $this->recordEvent($record, $user, 'break_start', $notes, $location);

            return $record->fresh();
        });
    }

    public function endBreak(User $user, ?string $notes = null, ?string $location = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $location) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'status' => 'in_progress',
            ]);

            $this->recordEvent($record, $user, 'break_end', $notes, $location);

            return $record->fresh();
        });
    }

    public function registerAbsence(User $user, ?int $shiftId = null, ?string $notes = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $shiftId, $notes) {
            $record = AttendanceRecord::create([
                'user_id'     => $user->id,
                'shift_id'    => $shiftId,
                'status'      => 'absent',
                'notes'       => $notes,
            ]);

            $this->recordEvent($record, $user, 'absence', $notes);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record;
        });
    }

    protected function findOpenRecord(User $user): AttendanceRecord
    {
        $record = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if (! $record) {
            throw new ModelNotFoundException('No active attendance record found.');
        }

        return $record;
    }

    protected function recordEvent(AttendanceRecord $record, User $user, string $type, ?string $notes = null, ?string $location = null): AttendanceEvent
    {
        return $record->events()->create([
            'user_id'     => $user->id,
            'type'        => $type,
            'occurred_at' => now(),
            'notes'       => $notes,
            'location'    => $location,
        ]);
    }
}
