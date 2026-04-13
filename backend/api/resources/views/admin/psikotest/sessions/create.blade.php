@extends('layouts.admin')

@section('title', 'Undang Peserta Test')
@section('page_title', 'Undang Peserta Test')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.psikotest.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.psikotest.sessions.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Source Selection --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sumber Data</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="source" value="manual" checked onchange="toggleSource('manual')" class="text-purple-600">
                        <span class="text-sm text-gray-700">Input Manual</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="source" value="applicant" onchange="toggleSource('applicant')" class="text-purple-600">
                        <span class="text-sm text-gray-700">Dari Pelamar</span>
                    </label>
                </div>
            </div>

            {{-- Applicant Selection --}}
            <div id="applicant-section" class="hidden">
                <label for="applicant_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Pelamar</label>
                <select name="applicant_id" id="applicant_id" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" onchange="fillFromApplicant()">
                    <option value="">-- Pilih Pelamar --</option>
                    @foreach($applicants as $applicant)
                        <option value="{{ $applicant->id }}" 
                                data-name="{{ $applicant->name }}"
                                data-email="{{ $applicant->email }}"
                                data-phone="{{ $applicant->phone }}">
                            {{ $applicant->name }} - {{ $applicant->email ?? 'Tidak ada email' }}
                        </option>
                    @endforeach
                </select>
                @if($applicants->isEmpty())
                    <p class="text-xs text-yellow-600 mt-1">Tidak ada pelamar yang tersedia</p>
                @endif
            </div>

            {{-- Manual Input --}}
            <div id="manual-section">
                <div>
                    <label for="participant_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Peserta <span class="text-red-500">*</span></label>
                    <input type="text" name="participant_name" id="participant_name" value="{{ old('participant_name') }}" 
                        class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="participant_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="participant_email" id="participant_email" value="{{ old('participant_email') }}" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="participant_phone" class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" name="participant_phone" id="participant_phone" value="{{ old('participant_phone') }}" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div>
                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Test <span class="text-red-500">*</span></label>
                <select name="test_type" id="test_type" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required onchange="updateQuestionSets()">
                    <option value="">Pilih jenis test</option>
                    <option value="kraepelin">Kraepelin</option>
                    <option value="papikostik">Papikostik</option>
                </select>
            </div>

            <div>
                <label for="question_set_id" class="block text-sm font-medium text-gray-700 mb-1">Set Soal <span class="text-red-500">*</span></label>
                <select name="question_set_id" id="question_set_id" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                    <option value="">Pilih jenis test dulu</option>
                </select>
            </div>

            <div>
                <label for="expires_days" class="block text-sm font-medium text-gray-700 mb-1">Berlaku (hari) <span class="text-red-500">*</span></label>
                <input type="number" name="expires_days" id="expires_days" value="{{ old('expires_days', 7) }}" 
                    min="1" max="30" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm" required>
                <p class="text-xs text-gray-500 mt-1">Link test akan kadaluarsa setelah hari yang ditentukan</p>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.psikotest.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Buat Undangan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const kraepelinSets = @json($kraepelinSets);
    const papikostikSets = @json($papikostikSets);

    function toggleSource(source) {
        const manual = document.getElementById('manual-section');
        const applicant = document.getElementById('applicant-section');
        
        if (source === 'applicant') {
            applicant.classList.remove('hidden');
        } else {
            applicant.classList.add('hidden');
            document.getElementById('applicant_id').value = '';
        }
    }

    function fillFromApplicant() {
        const select = document.getElementById('applicant_id');
        const option = select.options[select.selectedIndex];
        
        if (option && option.value) {
            document.getElementById('participant_name').value = option.dataset.name || '';
            document.getElementById('participant_email').value = option.dataset.email || '';
            document.getElementById('participant_phone').value = option.dataset.phone || '';
        }
    }

    function updateQuestionSets() {
        const type = document.getElementById('test_type').value;
        const select = document.getElementById('question_set_id');
        select.innerHTML = '';

        let sets = type === 'kraepelin' ? kraepelinSets : type === 'papikostik' ? papikostikSets : [];

        if (sets.length === 0) {
            select.innerHTML = '<option value="">Belum ada set soal aktif</option>';
            return;
        }

        sets.forEach(set => {
            const option = document.createElement('option');
            option.value = set.id;
            option.textContent = set.name;
            select.appendChild(option);
        });
    }
</script>
@endsection
