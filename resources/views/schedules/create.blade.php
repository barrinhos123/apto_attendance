<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Create schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl lg:max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 dark:border-slate-700">
                <form method="POST" action="{{ route('attendance.schedules.store') }}" class="p-6 space-y-4" id="schedule-form">
                    @csrf

                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" type="text" name="name" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{
                        clockIn: @js(old('clock_in_time', '09:00')),
                        clockOut: @js(old('clock_out_time', '17:00')),
                        get duration() {
                            const toMins = t => { const [h, m] = (t || '00:00').split(':').map(Number); return (h || 0) * 60 + (m || 0); };
                            const inM = toMins(this.clockIn), outM = toMins(this.clockOut);
                            const diff = outM > inM ? outM - inM : outM === inM ? 24 * 60 : (24 * 60 - inM) + outM;
                            const h = Math.floor(diff / 60), m = diff % 60;
                            return h ? (h + 'h' + (m ? ' ' + m + 'min' : '')) : (m + 'min');
                        }
                    }">
                        <div>
                            <x-label for="clock_in_time" value="{{ __('Clock in time') }}" />
                            <x-input id="clock_in_time" type="time" name="clock_in_time" class="mt-1 block w-full" :value="old('clock_in_time', '09:00')" x-model="clockIn" required />
                            <x-input-error for="clock_in_time" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="clock_out_time" value="{{ __('Clock out time') }}" />
                            <x-input id="clock_out_time" type="time" name="clock_out_time" class="mt-1 block w-full" :value="old('clock_out_time', '17:00')" x-model="clockOut" required />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Overnight shifts: use a time on the next day, e.g. 22:00 to 06:00 = 8h') }}</p>
                            <x-input-error for="clock_out_time" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ __('Duration') }}: </span>
                            <span class="font-medium text-slate-900 dark:text-slate-100" x-text="duration"></span>
                        </div>
                    </div>

                    <div>
                        <x-label value="{{ __('Days of the week') }}" />
                        <div class="mt-2 flex flex-wrap gap-4">
                            @foreach(\Apto\Attendance\Models\Schedule::weekdays() as $key => $label)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="days_of_week[]" value="{{ $key }}" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700"
                                        {{ in_array($key, old('days_of_week', [])) ? 'checked' : '' }}>
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error for="days_of_week" class="mt-2" />
                    </div>

                    <div x-data="{ locationType: @js(old('location_type', 'inside')) }">
                        <div>
                            <x-label value="{{ __('Location') }}" />
                            <div class="mt-2 flex gap-6">
                                @foreach(\Apto\Attendance\Models\Schedule::locationTypes() as $value => $label)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" name="location_type" value="{{ $value }}" class="border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700"
                                            {{ old('location_type', 'inside') === $value ? 'checked' : '' }}
                                            @change="locationType = $event.target.value; if ($event.target.value === 'outside') $nextTick(() => window.__aptoScheduleMapResize?.())">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Specific location shows a map to set an address.') }}</p>
                            <x-input-error for="location_type" class="mt-2" />
                        </div>

                        <div x-show="locationType === 'outside'" x-cloak x-transition>
                            <div class="mt-4">
                                <x-label for="place-search" value="{{ __('Specific place (optional)') }}" />
                                <input id="place-search" type="text" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Type an address or place name') }}" autocomplete="off" />
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Select from suggestions or click on the map to set the location.') }}</p>
                            </div>

                            <div id="map" class="mt-4 w-full h-64 sm:h-72 md:h-96 lg:h-[28rem] xl:h-[32rem] rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700" aria-hidden="true"></div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <x-label for="location" value="{{ __('Address / location') }}" />
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <x-input id="location" type="text" name="location" class="block w-full rounded-r-none" :value="old('location')" placeholder="{{ __('e.g. 123 Main St, City') }}" />
                                        <button type="button" id="find-from-address" class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm" title="{{ __('Find on map') }}">
                                            {{ __('Find') }}
                                        </button>
                                    </div>
                                    <x-input-error for="location" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="latitude" value="{{ __('Latitude') }}" />
                                    <x-input id="latitude" type="text" name="latitude" class="mt-1 block w-full" :value="old('latitude')" placeholder="e.g. 40.7128" inputmode="decimal" />
                                    <x-input-error for="latitude" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="longitude" value="{{ __('Longitude') }}" />
                                    <x-input id="longitude" type="text" name="longitude" class="mt-1 block w-full" :value="old('longitude')" placeholder="e.g. -74.0060" inputmode="decimal" />
                                    <x-input-error for="longitude" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-button type="submit">
                            {{ __('Create schedule') }}
                        </x-button>
                        <a href="{{ route('attendance.schedules.index') }}" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
(function() {
    const apiKey = @json(config('services.google_maps.api_key'));
    if (!apiKey) {
        const mapEl = document.getElementById('map');
        if (mapEl) mapEl.innerHTML = '<div class="flex items-center justify-center h-full text-slate-500">Google Maps API key not configured.</div>';
        window.__aptoScheduleMapResize = () => {};
        return;
    }

    const placeSearchInput = document.getElementById('place-search');
    const locationInput = document.getElementById('location');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const findFromAddressBtn = document.getElementById('find-from-address');

    let map;
    let marker;
    let autocomplete;
    let geocoder;

    function loadGoogleMaps() {
        return new Promise((resolve, reject) => {
            if (window.google && window.google.maps) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=window.__gmapsReady`;
            script.async = true;
            script.defer = true;
            window.__gmapsReady = resolve;
            script.onerror = () => reject(new Error('Failed to load Google Maps'));
            document.head.appendChild(script);
        });
    }

    function initMap() {
        const oldLat = parseFloat(latInput?.value) || 40.7128;
        const oldLng = parseFloat(lngInput?.value) || -74.0060;
        const defaultCenter = { lat: oldLat, lng: oldLng };
        map = new google.maps.Map(document.getElementById('map'), {
            center: defaultCenter,
            zoom: 10,
            mapTypeControl: true,
            fullscreenControl: true,
            zoomControl: true,
        });

        geocoder = new google.maps.Geocoder();

        if (latInput?.value && lngInput?.value) {
            setMarkerPosition(oldLat, oldLng);
        }

        map.addListener('click', (e) => {
            setPosition(e.latLng.lat(), e.latLng.lng(), true);
        });

        autocomplete = new google.maps.places.Autocomplete(placeSearchInput, {
            types: ['establishment', 'geocode'],
            fields: ['formatted_address', 'geometry', 'name'],
        });
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                const address = place.formatted_address || place.name || '';
                setPosition(lat, lng, false);
                locationInput.value = address;
                map.setCenter(place.geometry.location);
                map.setZoom(16);
            }
        });

        findFromAddressBtn?.addEventListener('click', () => {
            const address = locationInput.value.trim();
            if (!address) return;
            geocoder.geocode({ address }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const loc = results[0].geometry.location;
                    setPosition(loc.lat(), loc.lng(), false);
                    locationInput.value = results[0].formatted_address || address;
                    map.setCenter(loc);
                    map.setZoom(16);
                }
            });
        });

        function updateFromCoordinates() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                setMarkerPosition(lat, lng);
                map.setCenter({ lat, lng });
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        locationInput.value = results[0].formatted_address;
                    }
                });
            }
        }
        latInput?.addEventListener('change', updateFromCoordinates);
        lngInput?.addEventListener('change', updateFromCoordinates);

        window.addEventListener('resize', () => {
            google.maps.event.trigger(map, 'resize');
        });
    }

    function setMarkerPosition(lat, lng) {
        const pos = { lat, lng };
        if (marker) {
            marker.setPosition(pos);
        } else {
            marker = new google.maps.Marker({ map, position: pos, draggable: true });
            marker.addListener('dragend', () => {
                const p = marker.getPosition();
                setPosition(p.lat(), p.lng(), false);
            });
        }
    }

    function setPosition(lat, lng, reverseGeocode) {
        latInput.value = lat;
        lngInput.value = lng;
        setMarkerPosition(lat, lng);
        map.setCenter({ lat, lng });
        if (reverseGeocode) {
            geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    locationInput.value = results[0].formatted_address;
                }
            });
        }
    }

    loadGoogleMaps().then(() => {
        initMap();
        window.__aptoScheduleMapResize = () => { if (map) google.maps.event.trigger(map, 'resize'); };
    });
})();
    </script>
    @endpush
</x-app-layout>
