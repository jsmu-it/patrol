@extends('layouts.admin')

@section('title', 'Edit Project')
@section('page_title', 'Edit Project')

@section('content')
    <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-4 text-xs">
        @csrf
        @method('PUT')
        @include('admin.projects.form')
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('admin.projects.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
            <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Simpan</button>
        </div>
    </form>

    {{-- ================= Pengaturan Shift =================
         Shift milik project ini saja. Menambah/mengubah di sini tidak
         memengaruhi project lain. Form terpisah dari form project di atas
         karena HTML tidak mengizinkan form bersarang. --}}
    <section id="pengaturan-shift" class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-xs scroll-mt-6">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-3 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Pengaturan Shift</h2>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    Shift di bawah ini hanya berlaku untuk <span class="font-medium text-gray-700">{{ $project->name }}</span>.
                </p>
            </div>
            <span class="shrink-0 px-2 py-1 rounded bg-gray-100 text-gray-600 text-[11px]">{{ $project->shifts->count() }} shift</span>
        </div>

        @if($errors->has('shift'))
            <div class="mb-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 text-[11px]">
                {{ $errors->first('shift') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="py-2 pr-3 font-medium">Nama Shift</th>
                        <th class="py-2 pr-3 font-medium w-28">Jam Mulai</th>
                        <th class="py-2 pr-3 font-medium w-28">Jam Selesai</th>
                        <th class="py-2 pr-3 font-medium w-32">Toleransi (menit)</th>
                        <th class="py-2 text-right font-medium w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($project->shifts as $shift)
                        <tr class="border-b border-gray-50">
                            <form method="POST" action="{{ route('admin.projects.shifts.update', [$project, $shift]) }}" id="shift-{{ $shift->id }}">
                                @csrf
                                @method('PUT')
                            </form>
                            <td class="py-2 pr-3">
                                <input form="shift-{{ $shift->id }}" type="text" name="name" value="{{ $shift->name }}" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5">
                            </td>
                            <td class="py-2 pr-3">
                                <input form="shift-{{ $shift->id }}" type="time" name="start_time" value="{{ substr($shift->start_time, 0, 5) }}" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5">
                            </td>
                            <td class="py-2 pr-3">
                                <input form="shift-{{ $shift->id }}" type="time" name="end_time" value="{{ substr($shift->end_time, 0, 5) }}" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5">
                            </td>
                            <td class="py-2 pr-3">
                                <input form="shift-{{ $shift->id }}" type="number" name="tolerance_minutes" value="{{ $shift->tolerance_minutes }}" min="0" max="180" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5">
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button form="shift-{{ $shift->id }}" type="submit" class="px-2 py-1.5 rounded bg-slate-900 text-white">Simpan</button>
                                <form method="POST" action="{{ route('admin.projects.shifts.destroy', [$project, $shift]) }}" class="inline"
                                      onsubmit="return confirm('Hapus shift {{ $shift->name }} dari project ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1.5 rounded border border-gray-300 text-red-600">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">
                                Project ini belum punya shift. Tambahkan pada baris di bawah.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Baris tambah shift baru --}}
                    <tr class="bg-gray-50">
                        <form method="POST" action="{{ route('admin.projects.shifts.store', $project) }}" id="shift-baru">
                            @csrf
                        </form>
                        <td class="py-2 pr-3">
                            <input form="shift-baru" type="text" name="name" placeholder="mis. Pagi" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </td>
                        <td class="py-2 pr-3">
                            <input form="shift-baru" type="time" name="start_time" value="07:00" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </td>
                        <td class="py-2 pr-3">
                            <input form="shift-baru" type="time" name="end_time" value="15:00" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </td>
                        <td class="py-2 pr-3">
                            <input form="shift-baru" type="number" name="tolerance_minutes" value="10" min="0" max="180" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5">
                        </td>
                        <td class="py-2 text-right">
                            <button form="shift-baru" type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white">Tambah</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-[11px] text-gray-500">
            Shift yang sudah dipakai catatan absensi tidak bisa dihapus — ubah jamnya bila perlu penyesuaian.
        </p>
    </section>
@endsection
