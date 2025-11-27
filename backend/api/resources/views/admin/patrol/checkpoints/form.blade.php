@php($isEdit = isset($checkpoint))

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
    <div>
        <label class="block text-gray-600 mb-1">Project</label>
        <select name="project_id" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected((string)old('project_id', $checkpoint->project_id ?? request('project_id')) === (string)$project->id)>
                    {{ $project->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Nama Titik</label>
        <input type="text" name="title" value="{{ old('title', $checkpoint->title ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Pos</label>
        <input type="text" name="post_name" value="{{ old('post_name', $checkpoint->post_name ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5" required>
    </div>
    <div class="md:col-span-2">
        <label class="block text-gray-600 mb-1">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full border border-gray-300 rounded px-2 py-1.5">{{ old('description', $checkpoint->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Latitude</label>
        <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $checkpoint->latitude ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
    </div>
    <div>
        <label class="block text-gray-600 mb-1">Longitude</label>
        <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $checkpoint->longitude ?? '') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
    </div>
    @if($isEdit)
        <div class="md:col-span-2 text-[11px] text-gray-500">
            Kode QR: <span class="font-mono">{{ $checkpoint->code }}</span>
        </div>
    @endif
</div>

<div class="mt-4 text-xs">
    <label class="block text-gray-600 mb-1">Pilih Lokasi di Peta</label>
    <div class="mb-2 flex gap-2">
        <input id="checkpoint-search" type="text" placeholder="Cari alamat atau lokasi..." class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-xs">
        <button type="button" id="checkpoint-search-button" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">Cari</button>
    </div>
    <div id="checkpoint-search-results" class="mb-2 text-[11px] text-gray-700 space-y-1"></div>
    <div id="checkpoint-map" class="h-64 w-full rounded border border-gray-200"></div>
    <p class="mt-1 text-[11px] text-gray-500">Klik pada peta atau pilih hasil pencarian untuk mengisi koordinat latitude &amp; longitude lokasi patroli.</p>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('checkpoint-map');
            if (!mapElement) return;

            const latInput = document.querySelector('input[name="latitude"]');
            const lngInput = document.querySelector('input[name="longitude"]');

            const searchInput = document.getElementById('checkpoint-search');
            const searchButton = document.getElementById('checkpoint-search-button');
            const searchResults = document.getElementById('checkpoint-search-results');

            const defaultLat = parseFloat(latInput.value || '0') || 0;
            const defaultLng = parseFloat(lngInput.value || '0') || 0;

            const map = L.map('checkpoint-map').setView([defaultLat, defaultLng], (latInput.value && lngInput.value) ? 16 : 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker = null;
            if (latInput.value && lngInput.value) {
                marker = L.marker([defaultLat, defaultLng]).addTo(map);
            }

            map.on('click', function (e) {
                const { lat, lng } = e.latlng;
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });

            async function searchLocation() {
                const query = (searchInput?.value || '').trim();
                if (!query) return;

                if (searchResults) {
                    searchResults.innerHTML = 'Mencari...';
                }

                try {
                    const response = await fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=5&q=' + encodeURIComponent(query));
                    const results = await response.json();

                    if (!searchResults) return;
                    searchResults.innerHTML = '';

                    if (!Array.isArray(results) || results.length === 0) {
                        searchResults.textContent = 'Tidak ada hasil.';
                        return;
                    }

                    results.forEach(place => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'block w-full text-left px-2 py-1 rounded hover:bg-gray-100';
                        btn.textContent = place.display_name;
                        btn.addEventListener('click', () => {
                            const lat = parseFloat(place.lat);
                            const lng = parseFloat(place.lon);

                            if (!isNaN(lat) && !isNaN(lng)) {
                                latInput.value = lat.toFixed(7);
                                lngInput.value = lng.toFixed(7);

                                if (marker) {
                                    marker.setLatLng([lat, lng]);
                                } else {
                                    marker = L.marker([lat, lng]).addTo(map);
                                }

                                map.setView([lat, lng], 17);
                            }

                            searchResults.innerHTML = '';
                        });

                        searchResults.appendChild(btn);
                    });
                } catch (e) {
                    if (searchResults) {
                        searchResults.textContent = 'Terjadi kesalahan saat mencari lokasi.';
                    }
                }
            }

            if (searchButton && searchInput) {
                searchButton.addEventListener('click', searchLocation);
                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchLocation();
                    }
                });
            }
        });
    </script>
@endpush
