<div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                {{ __('Attendance Control') }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400">
                {{ __('Track clock-ins, breaks, and daily attendance for your workforce.') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3" x-data="{ clockingIn: false }">
            @if (! $activeRecord)
                <x-button @click="
                    clockingIn = true;
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => { $wire.clockIn(pos.coords.latitude, pos.coords.longitude, false); clockingIn = false; },
                            (err) => { $wire.clockIn(null, null, err.code === 1); clockingIn = false; },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    } else {
                        $wire.clockIn(null, null, false);
                        clockingIn = false;
                    }
                "
                x-bind:disabled="clockingIn"
                class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!clockingIn">{{ __('Clock in') }}</span>
                    <span x-show="clockingIn" x-cloak>{{ __('Getting location...') }}</span>
                </x-button>
            @else
                @if ($onBreak)
                    <x-button wire:click="endBreak" class="bg-slate-900 hover:bg-slate-800">
                        {{ __('End break') }}
                    </x-button>
                @else
                    <x-secondary-button wire:click="startBreak">
                        {{ __('Start break') }}
                    </x-secondary-button>
                @endif

                <x-danger-button wire:click="clockOut">
                    {{ __('Clock out') }}
                </x-danger-button>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
            <div class="p-6 space-y-4">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ __('Status') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <dl class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg p-4">
                        <dt class="text-sm text-slate-500 dark:text-slate-400">{{ __('Current state') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">
                            @if (! $activeRecord)
                                {{ __('Ready to clock in') }}
                            @elseif ($onBreak)
                                {{ __('On break since :time', ['time' => $activeRecord->events->where('type', 'break_start')->last()?->occurred_at?->format('H:i') ?? '—']) }}
                            @elseif ($activeRecord->status === 'in_progress')
                                {{ __('Working since :time', ['time' => $activeRecord->clock_in_at?->format('H:i') ?? '—']) }}
                            @else
                                {{ ucfirst(str_replace('_', ' ', $activeRecord->status)) }}
                            @endif
                        </dd>
                    </dl>

                    <dl class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg p-4">
                        <dt class="text-sm text-slate-500 dark:text-slate-400">{{ __('Expected hours today') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100">
                            {{ config('attendance.default_expected_daily_hours') }} {{ __('hours') }}
                        </dd>
                    </dl>
                </div>

                <div>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Schedule (optional)') }}</span>
                        <select wire:model.defer="scheduleId" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            <option value="">{{ __('Select a schedule') }}</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->id }}">{{ $schedule->name }}</option>
                            @endforeach
                        </select>
                        <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Assign the record to a schedule.') }}</span>
                    </label>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Notes (optional)') }}</span>
                    <textarea wire:model.defer="notes" rows="2" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm focus:border-slate-900 focus:ring-slate-900" placeholder="{{ __('Add context or remarks...') }}"></textarea>
                </label>

                @if ($errorsBag)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
                        <ul class="text-sm text-rose-700 list-disc list-inside space-y-1">
                            @foreach ($errorsBag as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ __('Daily summary') }}</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('Exports are generated at the end of the day for payroll and compliance reviews.') }}
            </p>
            <div class="flex flex-col gap-3">
                <a href="{{ route('attendance.exports.download', 'csv') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('attendance.exports.download', 'pdf') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                    {{ __('Export PDF') }}
                </a>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ __('Automations can be connected later to email these summaries automatically.') }}
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ __('Recent activity') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 uppercase tracking-wide text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left w-8"></th>
                        <th class="px-6 py-3 text-left">{{ __('Employee') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Shift') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Clock in') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Clock out') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Work') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Breaks') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Notes') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                @forelse ($recentRecords as $record)
                    <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                        <tr @click="expanded = !expanded" class="cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors" role="button" tabindex="0" @keydown.enter="expanded = !expanded" @keydown.space.prevent="expanded = !expanded">
                            <td class="px-6 py-4 w-8">
                                <span class="inline-block transition-transform duration-200" :class="expanded && 'rotate-90'">
                                    <svg class="size-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $record->user->name }}</td>
                            <td class="px-6 py-4">
                                @if ($record->schedule)
                                    {{ $record->schedule->name }}
                                @elseif ($record->shift)
                                    {{ $record->shift->scheduled_start?->format('M d H:i') }} – {{ $record->shift->scheduled_end?->format('H:i') }}
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">{{ __('Unassigned') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $record->clock_in_at?->format('M d, H:i') ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $record->clock_out_at?->format('M d, H:i') ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $record->formatDuration($record->total_work_seconds) }}</td>
                            <td class="px-6 py-4">{{ $record->formatDuration($record->total_break_seconds) }}</td>
                            <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $record->status) }}</td>
                            <td class="px-6 py-4">
                                @if ($record->notes)
                                    <span class="truncate block max-w-xs">{{ $record->notes }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4" @click.stop>
                                @if ($this->canClockOutRecord($record))
                                    <x-danger-button wire:click="clockOutRecord({{ $record->id }})" type="button" class="!text-xs !px-2 !py-1">
                                        {{ __('Clock out') }}
                                    </x-danger-button>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr x-show="expanded"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            x-cloak
                            class="bg-slate-50 dark:bg-slate-700">
                            <td colspan="10" class="px-6 py-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Employee') }}</dt><dd class="font-medium">{{ $record->user->name }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Email') }}</dt><dd>{{ $record->user->email }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Schedule') }}</dt><dd>{{ $record->schedule?->name ?? '—' }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Clock in') }}</dt><dd>{{ $record->clock_in_at?->format('M d, Y H:i:s') ?? '—' }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Clock out') }}</dt><dd>{{ $record->clock_out_at?->format('M d, Y H:i:s') ?? '—' }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Status') }}</dt><dd class="capitalize">{{ str_replace('_', ' ', $record->status) }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Total work time') }}</dt><dd>{{ $record->formatDuration($record->total_work_seconds) }}</dd></dl>
                                    <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Total break time') }}</dt><dd>{{ $record->formatDuration($record->total_break_seconds) }}</dd></dl>
                                    <dl class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">{{ __('Notes') }}</dt><dd>{{ $record->notes ?? '—' }}</dd></dl>
                                    @if ($record->latitude && $record->longitude)
                                        <dl><dt class="text-slate-500 dark:text-slate-400">{{ __('Coordinates') }}</dt><dd>{{ $record->latitude }}, {{ $record->longitude }}</dd></dl>
                                    @endif
                                </div>
                                @if ($record->events->isNotEmpty())
                                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                        <h4 class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase mb-2">{{ __('Events') }}</h4>
                                        <ul class="space-y-2">
                                            @foreach ($record->events->sortBy('occurred_at') as $event)
                                                <li class="flex items-start gap-3 text-sm">
                                                    <span class="text-slate-500 dark:text-slate-400 shrink-0">{{ $event->occurred_at->format('H:i:s') }}</span>
                                                    <span class="capitalize">{{ str_replace('_', ' ', $event->type) }}</span>
                                                    @if ($event->notes)
                                                        <span class="text-slate-600 dark:text-slate-400">– {{ $event->notes }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="10" class="px-6 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                {{ __('No attendance data recorded yet.') }}
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
    </div>
</div>
               merged truncated? Need continue 
