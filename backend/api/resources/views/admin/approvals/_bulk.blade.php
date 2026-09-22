{{--
    Bilah tindakan massal.
    Muncul hanya saat ada baris tercentang, menempel di bawah layar supaya
    tetap terjangkau meski daftarnya panjang.

    Parameter: $aksi (URL tujuan), $label (kata benda untuk pesan konfirmasi)
--}}
<div id="bilah-bulk"
     class="fixed inset-x-0 bottom-0 z-40 hidden border-t border-gray-200 bg-white px-4 py-3 shadow-lg">
    <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-gray-700">
            <span id="bulk-jumlah" class="font-semibold">0</span> {{ $label }} terpilih
            <button type="button" id="bulk-bersih" class="ml-3 text-xs text-gray-500 underline">bersihkan</button>
        </div>
        <div class="flex gap-2">
            <button type="submit" form="form-bulk" name="keputusan" value="approve"
                    class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Setujui Terpilih
            </button>
            <button type="submit" form="form-bulk" name="keputusan" value="reject"
                    class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Tolak Terpilih
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const bilah   = document.getElementById('bilah-bulk');
    const jumlah  = document.getElementById('bulk-jumlah');
    const semua   = document.getElementById('centang-semua');
    const bersih  = document.getElementById('bulk-bersih');
    const form    = document.getElementById('form-bulk');
    // Safari lama belum punya event.submitter, padahal kata kerja di kotak
    // konfirmasi harus benar — salah kata bisa membuat orang menolak padahal
    // niatnya menyetujui. Jadi tombol terakhir yang ditekan dicatat sendiri.
    let tombolTerakhir = null;
    const baris   = () => Array.from(document.querySelectorAll('.centang-baris'));

    function perbarui() {
        const terpilih = baris().filter(c => c.checked);
        jumlah.textContent = terpilih.length;
        bilah.classList.toggle('hidden', terpilih.length === 0);
        // Ruang di bawah daftar supaya baris terakhir tidak tertutup bilah.
        document.body.style.paddingBottom = terpilih.length ? '80px' : '';
        if (semua) {
            semua.checked = terpilih.length > 0 && terpilih.length === baris().length;
            semua.indeterminate = terpilih.length > 0 && terpilih.length < baris().length;
        }
    }

    document.querySelectorAll('button[form="form-bulk"]').forEach(b =>
        b.addEventListener('click', () => { tombolTerakhir = b.value; }));

    baris().forEach(c => c.addEventListener('change', perbarui));
    semua?.addEventListener('change', () => { baris().forEach(c => c.checked = semua.checked); perbarui(); });
    bersih?.addEventListener('click', () => { baris().forEach(c => c.checked = false); perbarui(); });

    form?.addEventListener('submit', (e) => {
        const terpilih = baris().filter(c => c.checked);
        const keputusan = e.submitter?.value ?? tombolTerakhir;
        const kata = keputusan === 'approve' ? 'menyetujui' : 'menolak';

        if (! confirm('Yakin ' + kata + ' ' + terpilih.length + ' {{ $label }} sekaligus?')) {
            e.preventDefault();
            return;
        }

        // Centangnya berada di dalam tabel, di luar formulir ini — kalau
        // ditaruh sebaris dengan tombol Approve/Reject per baris, formulirnya
        // jadi bersarang dan tidak sah. Jadi id-nya disalin saat dikirim.
        form.querySelectorAll('input[name="id[]"]').forEach(el => el.remove());
        terpilih.forEach(c => {
            const el = document.createElement('input');
            el.type = 'hidden';
            el.name = 'id[]';
            el.value = c.value;
            form.appendChild(el);
        });
    });

    perbarui();
})();
</script>
@endpush
