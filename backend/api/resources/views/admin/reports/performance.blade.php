@extends('layouts.admin')

@section('title', 'Kinerja Karyawan')
@section('page_title', 'Laporan Kinerja Karyawan')

@section('content')
<div x-data="{ 
    modalOpen: false, 
    selectedUser: null,
    openModal(user) {
        this.selectedUser = user;
        this.modalOpen = true;
    }
}">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gray-50">
            <form action="{{ route('admin.reports.performance') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="from" class="block text-xs font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="to" class="block text-xs font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="project_id" class="block text-xs font-medium text-gray-700 mb-1">Project</label>
                    <select name="project_id" id="project_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">Semua Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $filters['project_id'] == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition sm:text-sm">
                        Filter Data
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peringkat</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kehadiran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($performanceData as $index => $data)
                    @php
                        $profile = $data['user']->profile;
                        $userModalData = [
                            'name' => $data['user']->name,
                            'username' => $data['user']->username,
                            'nip' => $profile->nip ?? '-',
                            'project' => $data['user']->activeProject->name ?? 'Tanpa Project',
                            'position' => $profile->position ?? '-',
                            'join_date' => ($profile && $profile->join_date) ? $profile->join_date->format('d M Y') : '-',
                            'photo' => $profile && $profile->profile_photo_path 
                                ? asset('storage/' . $profile->profile_photo_path) 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($data['user']->name) . '&background=random',
                            'percentage' => $data['percentage'],
                            'present_days' => $data['present_days'],
                            'total_days' => $data['total_days'],
                            'status' => $data['percentage'] >= 90 ? 'Sangat Baik' : ($data['percentage'] >= 75 ? 'Baik' : ($data['percentage'] >= 50 ? 'Cukup' : 'Kurang')),
                            'status_color' => $data['percentage'] >= 90 ? 'green' : ($data['percentage'] >= 75 ? 'blue' : ($data['percentage'] >= 50 ? 'yellow' : 'red'))
                        ];
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index < 3 ? 'bg-yellow-100 text-yellow-800 font-bold' : 'bg-gray-100 text-gray-500' }}">
                                {{ $index + 1 }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="openModal(@js($userModalData))" class="text-left group">
                                <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition">{{ $data['user']->name }}</div>
                                <div class="text-xs text-gray-500">{{ $data['user']->username }}</div>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-50 text-blue-700">
                                {{ $data['user']->activeProject->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $data['present_days'] }} / {{ $data['total_days'] }} Hari
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 w-24 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $data['percentage'] >= 90 ? 'bg-green-500' : ($data['percentage'] >= 75 ? 'bg-blue-500' : ($data['percentage'] >= 50 ? 'bg-yellow-500' : 'bg-red-500')) }}" 
                                         style="width: {{ $data['percentage'] > 100 ? 100 : $data['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $data['percentage'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($data['percentage'] >= 90)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Sangat Baik</span>
                            @elseif($data['percentage'] >= 75)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Baik</span>
                            @elseif($data['percentage'] >= 50)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Cukup</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Kurang</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            Tidak ada data kinerja untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div x-show="modalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalOpen = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="modalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg leading-6 font-bold text-gray-900">Profil & Kinerja Karyawan</h3>
                                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <template x-if="selectedUser">
                                <div class="space-y-6">
                                    <!-- Header Profile -->
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                        <img :src="selectedUser.photo" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-sm" alt="">
                                        <div>
                                            <div class="text-xl font-bold text-gray-900" x-text="selectedUser.name"></div>
                                            <div class="text-sm text-gray-500" x-text="selectedUser.nip"></div>
                                            <div class="mt-1">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700" x-text="selectedUser.position"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3 border border-gray-100 rounded-lg">
                                            <div class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Project</div>
                                            <div class="text-sm font-semibold text-gray-800 mt-0.5" x-text="selectedUser.project"></div>
                                        </div>
                                        <div class="p-3 border border-gray-100 rounded-lg">
                                            <div class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Tanggal Bergabung</div>
                                            <div class="text-sm font-semibold text-gray-800 mt-0.5" x-text="selectedUser.join_date"></div>
                                        </div>
                                    </div>

                                    <!-- Performance Section -->
                                    <div class="p-4 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/50">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="text-sm font-bold text-gray-700">Estimasi Kinerja Bulanan</div>
                                            <span :class="`px-2 py-1 text-xs font-bold rounded-full bg-${selectedUser.status_color}-100 text-${selectedUser.status_color}-800`" 
                                                  x-text="selectedUser.status"></span>
                                        </div>
                                        
                                        <div class="flex items-end justify-between mb-2">
                                            <div class="text-3xl font-black text-gray-900">
                                                <span x-text="selectedUser.percentage"></span>%
                                            </div>
                                            <div class="text-right text-xs text-gray-500">
                                                Kehadiran: <span class="font-bold text-gray-800" x-text="selectedUser.present_days"></span> / <span x-text="selectedUser.total_days"></span> Hari
                                            </div>
                                        </div>

                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="h-3 rounded-full transition-all duration-1000" 
                                                 :class="`bg-${selectedUser.status_color}-500`"
                                                 :style="`width: ${selectedUser.percentage > 100 ? 100 : selectedUser.percentage}%`"></div>
                                        </div>
                                        
                                        <p class="mt-3 text-[11px] text-gray-400 leading-relaxed italic">
                                            * Persentase dihitung berdasarkan jumlah hari kehadiran (termasuk izin/cuti resmi) dibandingkan dengan total hari dalam periode yang dipilih.
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            @click="modalOpen = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
