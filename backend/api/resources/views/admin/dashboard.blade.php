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

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-800">Patroli Hari Ini</h2>
            </div>
            <div class="text-3xl font-semibold text-gray-900">{{ $patrolToday }}</div>
            <p class="mt-1 text-xs text-gray-500">Total log patroli hari ini</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-sm font-semibold text-gray-800 mb-2">Patroli Bulan Ini</div>
            <div class="text-3xl font-semibold text-gray-900">{{ $analytics['patrolStats']['thisMonth'] }}</div>
            <p class="mt-1 text-xs {{ $analytics['patrolStats']['percentChange'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $analytics['patrolStats']['percentChange'] >= 0 ? '+' : '' }}{{ $analytics['patrolStats']['percentChange'] }}% dari bulan lalu
            </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-sm font-semibold text-gray-800 mb-2">Checkpoint Coverage</div>
            <div class="text-3xl font-semibold text-gray-900">{{ $analytics['patrolStats']['coveragePercent'] }}%</div>
            <p class="mt-1 text-xs text-gray-500">{{ $analytics['patrolStats']['checkpointsVisited'] }}/{{ $analytics['patrolStats']['totalCheckpoints'] }} checkpoint</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-sm font-semibold text-gray-800 mb-2">Tipe Patroli (Bulan Ini)</div>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-center">
                    <div class="text-lg font-semibold text-blue-600">{{ $analytics['patrolByType']['patrol'] }}</div>
                    <div class="text-xs text-gray-500">Normal</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-red-600">{{ $analytics['patrolByType']['sos'] }}</div>
                    <div class="text-xs text-gray-500">SOS</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-yellow-600">{{ $analytics['patrolByType']['incident'] }}</div>
                    <div class="text-xs text-gray-500">Insiden</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Tren Absensi (30 Hari Terakhir)</h2>
            <canvas id="attendanceChart" height="120"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Absensi per Project (Bulan Ini)</h2>
            <canvas id="projectChart" height="200"></canvas>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const patrolPoints = @json($patrolPoints ?? []);
        const attendanceData = @json($analytics['attendanceByDay'] ?? ['labels' => [], 'clockIn' => [], 'clockOut' => []]);
        const projectData = @json($analytics['attendanceByProject'] ?? ['labels' => [], 'data' => []]);

        document.addEventListener('DOMContentLoaded', () => {
            // Patrol Map
            const mapElement = document.getElementById('patrol-map');
            if (mapElement && patrolPoints.length > 0) {
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
            } else if (mapElement) {
                mapElement.classList.add('flex', 'items-center', 'justify-center', 'text-xs', 'text-gray-400');
                mapElement.textContent = 'Belum ada titik patroli dengan koordinat.';
            }

            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                new Chart(attendanceCtx, {
                    type: 'line',
                    data: {
                        labels: attendanceData.labels,
                        datasets: [
                            {
                                label: 'Clock In',
                                data: attendanceData.clockIn,
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Clock Out',
                                data: attendanceData.clockOut,
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Project Chart
            const projectCtx = document.getElementById('projectChart');
            if (projectCtx && projectData.labels.length > 0) {
                new Chart(projectCtx, {
                    type: 'doughnut',
                    data: {
                        labels: projectData.labels,
                        datasets: [{
                            data: projectData.data,
                            backgroundColor: [
                                'rgb(59, 130, 246)',
                                'rgb(34, 197, 94)',
                                'rgb(249, 115, 22)',
                                'rgb(139, 92, 246)',
                                'rgb(236, 72, 153)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (projectCtx) {
                projectCtx.parentElement.innerHTML += '<p class="text-center text-gray-400 text-xs mt-4">Belum ada data</p>';
            }
        });
    </script>
@endpush
