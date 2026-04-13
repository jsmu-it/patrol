@extends('layouts.admin')

@section('title', 'Pengaturan PKWT')
@section('page_title', 'Pengaturan PKWT - ' . $project->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Template Editor --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <form action="{{ route('admin.projects.pkwt.update', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Title --}}
                    <div>
                        <label for="pkwt_title" class="block text-sm font-medium text-gray-700 mb-1">Judul Template PKWT</label>
                        <input type="text" name="pkwt_title" id="pkwt_title" 
                            value="{{ old('pkwt_title', $project->pkwt_title ?? 'Template PKWT ' . $project->name) }}"
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                            placeholder="Contoh: PKWT Security, PKWT Cleaning Service">
                    </div>

                    {{-- Import --}}
                    <div>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <label for="import_file" class="cursor-pointer px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                Import File (HTML/TXT/DOCX)
                            </label>
                            <input type="file" id="import_file" name="import_file" accept=".html,.txt,.docx,.doc" class="hidden">
                            <span class="text-xs text-gray-500 leading-6">Mendukung file .html, .txt, .doc, dan .docx</span>
                        </div>
                    </div>

                    {{-- Editor --}}
                    <div>
                        <label for="pkwt_template" class="block text-sm font-medium text-gray-700 mb-1">Isi Template PKWT</label>
                        <textarea name="pkwt_template" id="pkwt_template" rows="20" class="w-full border border-gray-300 rounded-md shadow-sm">{{ old('pkwt_template', $project->pkwt_template) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Placeholder Guide --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sticky top-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Panduan Placeholder
                </h3>
                
                <div class="text-xs text-gray-600 space-y-3">
                    <div class="bg-blue-50 border border-blue-100 rounded p-3">
                        <p class="font-medium text-blue-800 mb-1">Cara Penggunaan:</p>
                        <p>Tulis placeholder dengan format <code class="bg-blue-100 px-1 rounded">@{{nama_placeholder}}</code> di dalam template. Placeholder akan otomatis diganti dengan data karyawan saat PKWT dicetak.</p>
                    </div>
                    
                    <div>
                        <p class="font-semibold text-gray-800 mb-2">Data Pribadi:</p>
                        <div class="space-y-1 font-mono text-[11px]">
                            <div class="flex justify-between"><span class="text-green-700">@{{nama}}</span> <span class="text-gray-400">Nama lengkap</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{ktp}}</span> <span class="text-gray-400">No. KTP</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{tempat_lahir}}</span> <span class="text-gray-400">Tempat lahir</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{tanggal_lahir}}</span> <span class="text-gray-400">Tanggal lahir</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{alamat}}</span> <span class="text-gray-400">Alamat lengkap</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{email}}</span> <span class="text-gray-400">Alamat email</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{telepon}}</span> <span class="text-gray-400">No. telepon</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{jenis_kelamin}}</span> <span class="text-gray-400">Jenis kelamin</span></div>
                        </div>
                    </div>
                    
                    <div>
                        <p class="font-semibold text-gray-800 mb-2">Data Pekerjaan:</p>
                        <div class="space-y-1 font-mono text-[11px]">
                            <div class="flex justify-between"><span class="text-green-700">@{{pkwt_number}}</span> <span class="text-gray-400">No. PKWT</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{jabatan}}</span> <span class="text-gray-400">Nama jabatan</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{unit}}</span> <span class="text-gray-400">Nama project</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{tanggal_mulai}}</span> <span class="text-gray-400">Mulai kontrak</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{tanggal_akhir}}</span> <span class="text-gray-400">Akhir kontrak</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{durasi_kontrak}}</span> <span class="text-gray-400">Durasi (bulan)</span></div>
                        </div>
                    </div>
                    
                    <div>
                        <p class="font-semibold text-gray-800 mb-2">Data Pendapatan:</p>
                        <div class="space-y-1 font-mono text-[11px]">
                            <div class="flex justify-between"><span class="text-green-700">@{{gaji_pokok}}</span> <span class="text-gray-400">Gaji pokok</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{total_pendapatan}}</span> <span class="text-gray-400">Total pendapatan</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{total_potongan}}</span> <span class="text-gray-400">Total potongan</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{gaji_bersih}}</span> <span class="text-gray-400">Take home pay</span></div>
                        </div>
                    </div>
                    
                    <div>
                        <p class="font-semibold text-gray-800 mb-2">Data Lainnya:</p>
                        <div class="space-y-1 font-mono text-[11px]">
                            <div class="flex justify-between"><span class="text-green-700">@{{tanggal_sekarang}}</span> <span class="text-gray-400">Tanggal hari ini</span></div>
                            <div class="flex justify-between"><span class="text-green-700">@{{nama_perusahaan}}</span> <span class="text-gray-400">Nama perusahaan</span></div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-100 rounded p-3 mt-3">
                        <p class="font-medium text-yellow-800 mb-1">💡 Tips:</p>
                        <ul class="text-[11px] text-yellow-700 list-disc list-inside space-y-1">
                            <li>Pastikan placeholder ditulis persis seperti contoh</li>
                            <li>Gunakan kurung kurawal ganda @{{ }}</li>
                            <li>Placeholder bersifat case-sensitive</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let editorInstance;

            ClassicEditor
                .create(document.querySelector('#pkwt_template'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', '|', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
                })
                .then(editor => {
                    editorInstance = editor;
                })
                .catch(error => {
                    console.error(error);
                });

            // Handle Import
            const fileInput = document.getElementById('import_file');
            const form = fileInput.closest('form');
            
            fileInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const fileName = file.name.toLowerCase();
                
                // For .docx files, submit the form for conversion
                if (fileName.endsWith('.docx') || fileName.endsWith('.doc')) {
                    if (confirm('File DOCX akan diupload dan dikonversi. Lanjutkan?')) {
                        form.submit();
                    } else {
                        fileInput.value = '';
                    }
                    return;
                }
                
                // For HTML/TXT files, read directly
                const reader = new FileReader();
                reader.onload = (event) => {
                    const content = event.target.result;
                    if (editorInstance) {
                        editorInstance.setData(content);
                    } else {
                        document.querySelector('#pkwt_template').value = content;
                    }
                    alert('File berhasil diimport ke editor.');
                    fileInput.value = '';
                };
                reader.readAsText(file);
            });
        });
    </script>
    <style>
        .ck-editor__editable_inline {
            min-height: 500px;
        }
    </style>
@endpush
