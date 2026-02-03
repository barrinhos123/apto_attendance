<div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ __('Attendance Control') }}
            </h1>
            <p class="text-slate-600">
                {{ __('Track clock-ins, breaks, and daily attendance for your workforce.') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if (! $activeRecord)
                <x-button wire:click="clockIn" class="bg-slate-900 hover:bg-slate-800">
                    {{ __('Clock in') }}
                </x-button>
                <x-secondary-button wire:click="registerAbsence">
                    {{ __('Register absence') }}
                </x-secondary-button>
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
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="p-6 space-y-4">
                <h2 class="text-lg font-medium text-slate-900">{{ __('Status') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <dl class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <dt class="text-sm text-slate-500">{{ __('Current state') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-slate-900">
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

                    <dl class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <dt class="text-sm text-slate-500">{{ __('Expected hours today') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-slate-900">
                            {{ config('attendance.default_expected_daily_hours') }} {{ __('hours') }}
                        </dd>
                    </dl>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">{{ __('Shift (optional)') }}</span>
                        <x-input type="number" wire:model.defer="shiftId" min="1" class="mt-1 w-full" placeholder="{{ __('Shift ID') }}" />
                        <span class="mt-1 text-xs text-slate-500">{{ __('Assign the record to a planned shift.') }}</span>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">{{ __('Location (optional)') }}</span>
                        <x-input type="text" wire:model.defer="location" class="mt-1 w-full" placeholder="{{ __('e.g. HQ Office, GPS coords') }}" />
                    </label>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Notes (optional)') }}</span>
                    <textarea wire:model.defer="notes" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900" placeholder="{{ __('Add context or remarks...') }}"></textarea>
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

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-medium text-slate-900">{{ __('Daily summary') }}</h2>
            <p class="text-sm text-slate-600">
                {{ __('Exports are generated at the end of the day for payroll and compliance reviews.') }}
            </p>
            <div class="flex flex-col gap-3">
                <a href="{{ route('attendance.exports.download', 'csv') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('attendance.exports.download', 'pdf') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('Export PDF') }}
                </a>
            </div>
            <p class="text-xs text-slate-500">
                {{ __('Automations can be connected later to email these summaries automatically.') }}
            </p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-lg font-medium text-slate-900">{{ __('Recent activity') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">{{ __('Employee') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Shift') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Clock in') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Clock out') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentRecords as $record)
                        <tr>
                            <td class="px-6 py-4">{{ $record->user->name }}</td>
                            <td class="px-6 py-4">
                                @if ($record->shift)
                                    {{ $record->shift->scheduled_start?->format('M d H:i') }} – {{ $record->shift->scheduled_end?->format('H:i') }}
                                @else
                                    <span class="text-slate-400">{{ __('Unassigned') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $record->clock_in_at?->format('M d, H:i') ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $record->clock_out_at?->format('M d, H:i') ?? '—' }}</td>
                            <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $record->status) }}</td>
                            <td class="px-6 py-4">
                                @if ($record->notes)
                                    <span class="truncate block max-w-xs">{{ $record->notes }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">
                                {{ __('No attendance data recorded yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
               merged truncated? Need continue 
