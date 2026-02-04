<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                {{ __('Schedules') }}
            </h2>
            @if (auth()->user()->business_id)
            <a href="{{ route('attendance.schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 dark:bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition ease-in-out duration-150">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Create schedule') }}
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <form id="filter-form-schedules" method="GET" action="{{ route('attendance.schedules.index') }}"></form>
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Name') }}</th>
                                    @if (!auth()->user()->business_id)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Business') }}</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Duration') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Location') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Place') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                                <tr class="bg-slate-100 dark:bg-slate-700">
                                    <td class="px-4 py-2">
                                        <input form="filter-form-schedules" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Name') }}" class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </td>
                                    @if (!auth()->user()->business_id)
                                        <td class="px-4 py-2"></td>
                                    @endif
                                    <td class="px-4 py-2"></td>
                                    <td class="px-4 py-2"></td>
                                    <td class="px-4 py-2">
                                        <select form="filter-form-schedules" name="location_type" class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 text-sm">
                                            <option value="">{{ __('All') }}</option>
                                            <option value="inside" {{ request('location_type') === 'inside' ? 'selected' : '' }}>{{ __('Inside') }}</option>
                                            <option value="outside" {{ request('location_type') === 'outside' ? 'selected' : '' }}>{{ __('Outside') }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2"></td>
                                    <td class="px-4 py-2 text-right">
                                        <button form="filter-form-schedules" type="submit" class="inline-flex items-center px-3 py-1.5 bg-slate-800 dark:bg-slate-600 text-white rounded-md text-xs font-medium hover:bg-slate-700 dark:hover:bg-slate-500">{{ __('Filter') }}</button>
                                        <a href="{{ route('attendance.schedules.index') }}" class="inline-flex items-center px-3 py-1.5 ml-1 border border-slate-300 dark:border-slate-600 rounded-md text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600">{{ __('Reset') }}</a>
                                    </td>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($schedules as $schedule)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $schedule->name }}</td>
                                        @if (!auth()->user()->business_id)
                                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $schedule->business?->name ?? '—' }}</td>
                                        @endif
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            @if ($schedule->duration_minutes >= 60)
                                                {{ (int) floor($schedule->duration_minutes / 60) }}h @if ($schedule->duration_minutes % 60){{ $schedule->duration_minutes % 60 }}min @endif
                                            @else
                                                {{ $schedule->duration_minutes }} {{ __('min') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            @php
                                                $days = \Apto\Attendance\Models\Schedule::weekdays();
                                                $labels = array_map(fn ($key) => $days[$key] ?? $key, $schedule->days_of_week ?? []);
                                            @endphp
                                            {{ implode(', ', $labels) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                            {{ $schedule->location_type === 'inside' ? __('Inside') : __('Outside') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 max-w-[200px] truncate" title="{{ $schedule->location ?? '' }}">
                                            {{ $schedule->location ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <a href="{{ route('attendance.schedules.edit', $schedule) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('attendance.schedules.destroy', $schedule) }}" class="inline-block ml-2" onsubmit="return confirm('{{ __('Are you sure you want to delete this schedule?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->business_id ? 6 : 7 }}" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            {{ __('No schedules found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
