<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Livewire;

use Livewire\Component;
use Apto\Attendance\Models\AttendanceRecord;
use Apto\Attendance\Services\AttendanceRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AttendanceDashboard extends Component
{
    public ?int $userId = null;
    public ?int $shiftId = null;
    public ?string $notes = null;
    public ?string $location = null;

    public array $errorsBag = [];

    public function mount(): void
    {
        $this->userId = auth()->id();
    }

    public function clockIn(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->clockIn(auth()->user(), $this->shiftId, $this->notes, $this->location);
            $this->resetInputFields();
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function clockOut(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->clockOut(auth()->user(), $this->notes, $this->location);
            $this->resetInputFields();
        } catch (ModelNotFoundException $e) {
            $this->errorsBag[] = __('No active attendance record found to clock-out.');
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function startBreak(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->startBreak(auth()->user(), $this->notes, $this->location);
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
            $recorder->endBreak(auth()->user(), $this->notes, $this->location);
        } catch (ModelNotFoundException $e) {
            $this->errorsBag[] = __('No active attendance record found to end a break.');
        } catch (Throwable $e) {
            $this->errorsBag[] = $e->getMessage();
        }
    }

    public function registerAbsence(AttendanceRecorder $recorder): void
    {
        $this->resetValidationState();

        try {
            $recorder->registerAbsence(auth()->user(), $this->shiftId, $this->notes);
            $this->resetInputFields();
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
        $this->location = null;
        $this->shiftId = null;
    }

    protected function resetValidationState(): void
    {
        $this->errorsBag = [];
    }

    public function render(): View
    {
        $recentRecords = AttendanceRecord::query()
            ->with(['user', 'shift'])
            ->latest('clock_in_at')
            ->limit(10)
            ->get();

        return view('attendance::livewire.dashboard', [
            'recentRecords' => $recentRecords,
            'activeRecord'  => $this->activeRecord,
            'onBreak'       => $this->onBreak,
        ])->layout('layouts.app');
    }
}
