<?php

declare(strict_types=1);

namespace Apto\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Apto\Attendance\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Schedule::with('business');

        if ($request->user()->business_id) {
            $query->where('business_id', $request->user()->business_id);
        }

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->string('search')->trim() . '%';
            $q->where('name', 'like', $term);
        });
        $query->when($request->filled('location_type') && in_array($request->string('location_type')->toString(), ['inside', 'outside'], true), function ($q) use ($request) {
            $q->where('location_type', $request->string('location_type')->toString());
        });

        $schedules = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('attendance::schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        return view('attendance::schedules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_hours' => ['required', 'integer', 'min:0', 'max:24'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'location_type' => ['required', 'string', 'in:inside,outside'],
            'location' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $durationMinutes = (int) $validated['duration_hours'] * 60 + (int) $validated['duration_minutes'];
        if ($durationMinutes < 1) {
            return back()->withErrors(['duration_hours' => __('Duration must be at least 1 minute.')]);
        }

        $businessId = $request->user()->business_id;
        if (! $businessId) {
            return back()->withErrors(['business' => __('You must be assigned to a business before creating schedules. Contact a super admin.')]);
        }

        Schedule::create([
            'business_id' => $businessId,
            'name' => $validated['name'],
            'duration_minutes' => $durationMinutes,
            'days_of_week' => $validated['days_of_week'],
            'location_type' => $validated['location_type'],
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return redirect()->route('attendance.schedules.index')
            ->with('success', __('Schedule created successfully.'));
    }

    public function edit(Request $request, Schedule $schedule): View
    {
        $this->authorizeSchedule($request, $schedule);

        return view('attendance::schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorizeSchedule($request, $schedule);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_hours' => ['required', 'integer', 'min:0', 'max:24'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'days_of_week' => ['required', 'array', 'min:1'],
            'days_of_week.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'location_type' => ['required', 'string', 'in:inside,outside'],
            'location' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $durationMinutes = (int) $validated['duration_hours'] * 60 + (int) $validated['duration_minutes'];
        if ($durationMinutes < 1) {
            return back()->withErrors(['duration_hours' => __('Duration must be at least 1 minute.')]);
        }

        $schedule->update([
            'name' => $validated['name'],
            'duration_minutes' => $durationMinutes,
            'days_of_week' => $validated['days_of_week'],
            'location_type' => $validated['location_type'],
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return redirect()->route('attendance.schedules.index')
            ->with('success', __('Schedule updated successfully.'));
    }

    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorizeSchedule($request, $schedule);

        $schedule->delete();

        return redirect()->route('attendance.schedules.index')
            ->with('success', __('Schedule deleted successfully.'));
    }

    private function authorizeSchedule(Request $request, Schedule $schedule): void
    {
        $user = $request->user();
        if ($user->business_id && $schedule->business_id !== $user->business_id) {
            abort(403, __('You do not have permission to access this schedule.'));
        }
    }
}
