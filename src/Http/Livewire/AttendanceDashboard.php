<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Livewire;

use Apto\Attendance\Models\Schedule;
use Livewire\Component;
use Apto\Attendance\Models\AttendanceRecord;
use Apto\Attendance\Services\AttendanceRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AttendanceDashboard extends Component
{
    public ?int $userId = null;

    public ?int $scheduleId = null;

    public ?string $notes = null;

    public array $errorsBag = [];

    public function mount(): void
    {
        $this->userId = auth()->id();
    }

    public function clockIn(?float $latitude = null, ?float $longitude = null, bool $locationPermissionDenied = false, AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->clockIn(auth()->user(), $this->scheduleId, $this->notes, $latitude, $longitude, $locationPermissionDenied);
            $this->resetInputFields();
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function clockOut(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->clockOut(auth()->user(), $this->notes);
            $this->resetInputFields();
        } catch (ModelNotFoundException $e) {
            $this->errorsBag[] = __('No active attendance record found to clock-out.');
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function clockOutRecord(int $recordId, AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $record = AttendanceRecord::findOrFail($recordId);
            $recorder->clockOutRecord($record, auth()->user(), $this->notes);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->errorsBag[] = $e->getMessage();
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function startBreak(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->startBreak(auth()->user(), $this->notes);
        } catch (ModelNotFoundException $e) {
            $this->errorsBag[] = __('No active attendance record found to start a break.');
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function endBreak(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->endBreak(auth()->user(), $this->notes);
        } catch (ModelNotFoundException $e) {
            $this->errorsBag[] = __('No active attendance record found to end a break.');
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function getActiveRecordProperty(): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->where('user_id', $this->userId)
            ->whereNull('clock_out_at')
            ->with('events')
            ->latest('clock_in_at')
            ->first();
    }

    public function getOnBreakProperty(): bool
    {
        return optional($this->activeRecord)->status === 'on_break';
    }

    protected function resetInputFields(): void
    {
        $this->notes = null;
        $this->scheduleId = null;
    }

    public function canClockOutRecord(AttendanceRecord $record): bool
    {
        if ($record->clock_out_at !== null) {
            return false;
        }

        return $record->user_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin();
    }

    protected function resetValidationState(): void
    {
        $this->errorsBag = [];
    }

    public function render(): View
    {
        $recentRecords = AttendanceRecord::query()
            ->with(['user', 'shift', 'schedule', 'events'])
            ->latest('clock_in_at')
            ->limit(10)
            ->get();

        $schedules = auth()->user()->business_id
            ? Schedule::where('business_id', auth()->user()->business_id)->orderBy('name')->get()
            : collect();

        return view('attendance::livewire.dashboard', [
            'recentRecords' => $recentRecords,
            'activeRecord'  => $this->activeRecord,
            'onBreak'       => $this->onBreak,
            'schedules'     => $schedules,
        ])->layout('layouts.app');
    }
}
