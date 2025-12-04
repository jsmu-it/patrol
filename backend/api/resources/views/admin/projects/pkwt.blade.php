@extends('layouts.admin')

@section('title', 'Pengaturan PKWT')
@section('page_title', 'Pengaturan PKWT - ' . $project->name)

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <form action="{{ route('admin.projects.pkwt.update', $project) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="pkwt_template" class="block text-sm font-medium text-gray-700">Template PKWT</label>
                    <div>
                        <label for="import_file" class="cursor-pointer px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Import PKWT (HTML/TXT)
                        </label>
                        <input type="file" id="import_file" accept=".html,.txt" class="hidden">
                    </div>
                </div>
                <textarea name="pkwt_template" id="pkwt_template" rows="15" class="w-full border border-gray-300 rounded-md shadow-sm">{{ old('pkwt_template', $project->pkwt_template) }}</textarea>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm">Batal</a>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800 text-sm">Simpan Template</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let editorInstance;

            ClassicEditor
                .create(document.querySelector('#pkwt_template'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
                })
                .then(editor => {
                    editorInstance = editor;
                })
                .catch(error => {
                    console.error(error);
                });

            // Handle Import
            const fileInput = document.getElementById('import_file');
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (event) => {
                    const content = event.target.result;
                    if (editorInstance) {
                        editorInstance.setData(content);
                    } else {
                        document.querySelector('#pkwt_template').value = content;
                    }
                    alert('File berhasil diimport ke editor.');
                    fileInput.value = ''; // Reset
                };
                reader.readAsText(file);
            });
        });
    </script>
    <style>
        .ck-editor__editable_inline {
            min-height: 400px;
        }
    </style>
@endpush
