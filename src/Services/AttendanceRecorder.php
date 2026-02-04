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
    public function clockIn(User $user, ?int $scheduleId = null, ?string $notes = null, ?float $latitude = null, ?float $longitude = null, bool $locationPermissionDenied = false): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $scheduleId, $notes, $latitude, $longitude, $locationPermissionDenied) {
            $record = AttendanceRecord::create([
                'user_id'                    => $user->id,
                'shift_id'                   => null,
                'schedule_id'                => $scheduleId,
                'clock_in_at'                => now(),
                'status'                     => 'in_progress',
                'notes'                      => $notes,
                'latitude'                   => $latitude,
                'longitude'                  => $longitude,
                'location_permission_denied' => $locationPermissionDenied,
            ]);

            $this->recordEvent($record, $user, 'clock_in', $notes, $latitude, $longitude);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record;
        });
    }

    public function clockOut(User $user, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $latitude, $longitude) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'clock_out_at' => now(),
                'status'       => 'completed',
                'notes'        => $notes ?? $record->notes,
            ]);

            $this->recordEvent($record, $user, 'clock_out', $notes, $latitude, $longitude);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record->fresh();
        });
    }

    public function clockOutRecord(AttendanceRecord $record, User $actor, ?string $notes = null): AttendanceRecord
    {
        if ($record->clock_out_at !== null) {
            throw new \InvalidArgumentException(__('This record is already clocked out.'));
        }

        if ($record->user_id !== $actor->id && ! $actor->isAdmin() && ! $actor->isSuperAdmin()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(__('You may only clock out your own records.'));
        }

        return DB::transaction(function () use ($record, $actor, $notes) {
            $record->update([
                'clock_out_at' => now(),
                'status'       => 'completed',
                'notes'        => $notes ?? $record->notes,
            ]);

            $this->recordEvent($record, $actor, 'clock_out', $notes, $record->latitude, $record->longitude);

            AttendanceRecorded::dispatch($record->fresh('user'));

            return $record->fresh();
        });
    }

    public function startBreak(User $user, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $latitude, $longitude) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'status' => 'on_break',
            ]);

            $this->recordEvent($record, $user, 'break_start', $notes, $latitude, $longitude);

            return $record->fresh();
        });
    }

    public function endBreak(User $user, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): AttendanceRecord
    {
        return DB::transaction(function () use ($user, $notes, $latitude, $longitude) {
            $record = $this->findOpenRecord($user);

            $record->update([
                'status' => 'in_progress',
            ]);

            $this->recordEvent($record, $user, 'break_end', $notes, $latitude, $longitude);

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

    protected function recordEvent(AttendanceRecord $record, User $user, string $type, ?string $notes = null, ?float $latitude = null, ?float $longitude = null): AttendanceEvent
    {
        return $record->events()->create([
            'user_id' => $user->id,
            'type'    => $type,
            'occurred_at' => now(),
            'notes'   => $notes,
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
