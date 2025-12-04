@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs text-gray-500">Total Guard</div>
            <div class="mt-1 text-2xl font-semibold text-gray-800">{{ $totalGuards }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs text-gray-500">Total Admin</div>
            <div class="mt-1 text-2xl font-semibold text-gray-800">{{ $totalAdmins }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs text-gray-500">Total Project</div>
            <div class="mt-1 text-2xl font-semibold text-gray-800">{{ $totalProjects }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs text-gray-500">Absensi Hari Ini</div>
            <div class="mt-1 flex items-center justify-between">
                <span class="text-2xl font-semibold text-gray-800">{{ $attendanceToday }}</span>
                <button onclick="document.getElementById('attendance-modal').classList.remove('hidden')" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">Lihat Detail</button>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div id="attendance-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Absensi Hari Ini</h3>
                <button onclick="document.getElementById('attendance-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($todayAttendanceList as $log)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $log['time'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap font-medium">{{ $log['user_name'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $log['project_name'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $log['type'] === 'Masuk' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $log['type'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ $log['shift'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-800">Patroli Hari Ini</h2>
            </div>
            <div class="text-3xl font-semibold text-gray-900">{{ $patrolToday }}</div>
            <p class="mt-1 text-xs text-gray-500">Total log patroli yang tercatat hari ini.</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800 mb-2">Ringkasan Cepat</h2>
            <ul class="text-xs text-gray-600 space-y-1 list-disc pl-4">
                <li>Kelola data karyawan dan project di menu samping.</li>
                <li>Pantau absensi dan patroli melalui menu laporan.</li>
                <li>Proses approval absensi dinas dan cuti di menu Approval.</li>
            </ul>
        </div>
    </div>
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Peta Titik Patroli</h2>
        <div id="patrol-map" class="h-72 w-full rounded border border-gray-200"></div>
        <p class="mt-2 text-[11px] text-gray-500">Setiap marker merepresentasikan lokasi checkpoint patroli per project.</p>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const patrolPoints = @json($patrolPoints ?? []);

        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('patrol-map');
            if (! mapElement || patrolPoints.length === 0) {
                if (mapElement) {
                    mapElement.classList.add('flex', 'items-center', 'justify-center', 'text-xs', 'text-gray-400');
                    mapElement.textContent = 'Belum ada titik patroli dengan koordinat.';
                }
                return;
            }

            const map = L.map('patrol-map');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const latLngs = [];

            patrolPoints.forEach(point => {
                const latLng = [point.lat, point.lng];
                latLngs.push(latLng);
                L.marker(latLng).addTo(map).bindPopup(
                    `<strong>${point.title}</strong><br/>${point.post_name || ''}<br/><span class="text-xs">${point.project || ''}</span>`
                );
            });

            if (latLngs.length === 1) {
                map.setView(latLngs[0], 16);
            } else {
                const bounds = L.latLngBounds(latLngs);
                map.fitBounds(bounds.pad(0.2));
            }
        });
    </script>
@endpush
