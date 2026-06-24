@props([
    'address' => 'location',
    'latitude' => 'latitude',
    'longitude' => 'longitude',
    'maplink' => 'google_map_link',
    'addressValue' => '',
    'latitudeValue' => '',
    'longitudeValue' => '',
    'maplinkValue' => '',
])

<div x-data="ravionMapPicker({
        address: @js(old($address, $addressValue)),
        latitude: @js(old($latitude, $latitudeValue)),
        longitude: @js(old($longitude, $longitudeValue)),
        maplink: @js(old($maplink, $maplinkValue))
    })"
    x-init="init()"
    class="space-y-3">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Location / Address
            </label>

            <input type="text"
                   name="{{ $address }}"
                   x-model="address"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-10"
                   placeholder="Search or type project location">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Latitude
            </label>

            <input type="text"
                   name="{{ $latitude }}"
                   x-model="latitude"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-10"
                   readonly>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Longitude
            </label>

            <input type="text"
                   name="{{ $longitude }}"
                   x-model="longitude"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-10"
                   readonly>
        </div>

        <input type="hidden"
               name="{{ $maplink }}"
               x-model="maplink">

        <div class="md:col-span-4 flex flex-wrap gap-2">
            <button type="button"
                    @click="openPicker()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                Pick Location
            </button>

            <template x-if="maplink">
                <a :href="maplink"
                   target="_blank"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Open Google Map
                </a>
            </template>

            <template x-if="maplink">
                <button type="button"
                        @click="copyMapLink()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
                    Copy Map URL
                </button>
            </template>

            <span x-show="copied"
                  class="text-green-700 text-sm font-semibold py-2">
                Copied!
            </span>
        </div>

        <template x-if="address || latitude">
            <div class="md:col-span-4 bg-green-50 border border-green-200 rounded-md p-3 text-sm text-green-800">
                Location selected:
                <span class="font-semibold" x-text="address || 'Coordinates selected'"></span>
            </div>
        </template>

    </div>

    {{-- MODAL --}}
    <div x-show="showModal"
         style="display:none;"
         class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl overflow-hidden">

            <div class="flex justify-between items-center px-5 py-3 border-b">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Select Project Location
                    </h3>
                    <p class="text-sm text-gray-500">
                        Search location or click on map to drop the pin.
                    </p>
                </div>

                <button type="button"
                        @click="showModal = false"
                        class="text-gray-500 hover:text-gray-800 text-2xl">
                    &times;
                </button>
            </div>

            <div class="p-4">

                <div class="flex gap-2 mb-3">
                    <input type="text"
                           x-model="searchQuery"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-10"
                           placeholder="Example: Zaks Meadows Hyderabad">

                    <button type="button"
                            @click="searchLocation()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                        Search
                    </button>
                </div>

                <div id="ravion-map-picker"
                     class="w-full h-[480px] rounded-md border border-gray-300">
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="md:col-span-3">
                        <strong>Address:</strong>
                        <span x-text="tempAddress || '-'"></span>
                    </div>

                    <div>
                        <strong>Latitude:</strong>
                        <span x-text="tempLatitude || '-'"></span>
                    </div>

                    <div>
                        <strong>Longitude:</strong>
                        <span x-text="tempLongitude || '-'"></span>
                    </div>

                    <div class="text-right">
                        <button type="button"
                                @click="confirmLocation()"
                                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                            Use This Location
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@once
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        function ravionMapPicker(initial) {
            return {
                showModal: false,
                copied: false,
                searchQuery: '',

                address: initial.address || '',
                latitude: initial.latitude || '',
                longitude: initial.longitude || '',
                maplink: initial.maplink || '',

                tempAddress: initial.address || '',
                tempLatitude: initial.latitude || '',
                tempLongitude: initial.longitude || '',

                map: null,
                marker: null,

                init() {
                    if (this.latitude && this.longitude && !this.maplink) {
                        this.maplink = this.generateMapLink(this.latitude, this.longitude);
                    }
                },

                openPicker() {
                    this.showModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            const lat = parseFloat(this.latitude) || 17.3850;
                            const lng = parseFloat(this.longitude) || 78.4867;

                            if (!this.map) {
                                this.map = L.map('ravion-map-picker').setView([lat, lng], 13);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.map);

                                this.map.on('click', (e) => {
                                    this.setTempLocation(e.latlng.lat, e.latlng.lng, true);
                                });
                            }

                            this.map.invalidateSize();
                            this.map.setView([lat, lng], 13);

                            if (this.latitude && this.longitude) {
                                this.setTempLocation(lat, lng, false);
                            }
                        }, 300);
                    });
                },

                generateMapLink(lat, lng) {
                    return `https://www.google.com/maps?q=${lat},${lng}`;
                },

                setTempLocation(lat, lng, reverseAddress = true) {
                    this.tempLatitude = Number(lat).toFixed(7);
                    this.tempLongitude = Number(lng).toFixed(7);

                    if (this.marker) {
                        this.marker.setLatLng([this.tempLatitude, this.tempLongitude]);
                    } else {
                        this.marker = L.marker([this.tempLatitude, this.tempLongitude]).addTo(this.map);
                    }

                    if (reverseAddress) {
                        this.reverseGeocode(this.tempLatitude, this.tempLongitude);
                    }
                },

                reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(response => response.json())
                        .then(data => {
                            this.tempAddress = data.display_name || this.tempAddress;
                        })
                        .catch(() => {});
                },

                searchLocation() {
                    if (!this.searchQuery) {
                        return;
                    }

                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.length) {
                                alert('Location not found.');
                                return;
                            }

                            const result = data[0];

                            this.tempAddress = result.display_name;
                            this.setTempLocation(result.lat, result.lon, false);

                            this.map.setView([result.lat, result.lon], 16);
                        })
                        .catch(() => {
                            alert('Unable to search location right now.');
                        });
                },

                confirmLocation() {
                    if (!this.tempLatitude || !this.tempLongitude) {
                        alert('Please select a location.');
                        return;
                    }

                    this.address = this.tempAddress;
                    this.latitude = this.tempLatitude;
                    this.longitude = this.tempLongitude;
                    this.maplink = this.generateMapLink(this.latitude, this.longitude);

                    this.showModal = false;
                },

                copyMapLink() {
                    navigator.clipboard.writeText(this.maplink);

                    this.copied = true;

                    setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                }
            }
        }
    </script>
@endonce