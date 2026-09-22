<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PbgRequest;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\PbgDiajukan;
use App\Services\ArsipDivisi;
use App\Services\Penyetuju;
use App\Services\PenyetujuDokumen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PBG — Permintaan Barang Gudang (GA/FM-01-01).
 *
 * Mengikuti kolom tanda tangan formulir cetak: pemohon mengajukan, kepala
 * bagian menyetujui, lalu gudang yang bernaung di divisi HR & GA memproses dan
 * menyerahkan barangnya. Salinan PDF disimpan di folder PBG milik divisi
 * HR & GA dan diperbarui setiap status berubah.
 */
class PbgController extends Controller
{
    private const DIVISI_GUDANG = 'HR & GA';
    private const MAKS_BARIS    = 30;

    /** Jabatan yang mengurus gudang, untuk menyempitkan pemberitahuan. */
    private const KATA_GUDANG = '/\b(gudang|logistic|logistik|ga|hrga)\b/i';

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = PbgRequest::with('user:id,name')->latest();

        // Orang gudang melihat semuanya; karyawan lain hanya permintaannya
        // sendiri ditambah yang menunggu persetujuannya.
        if (! $this->orangGudang($user)) {
            $query->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('id', $this->idMenungguSaya($user)));
        }

        return view('portal.pbg.index', [
            'daftar' => $query->paginate(15),
            'semua'  => $this->orangGudang($user),
        ]);
    }

    public function create(Request $request, PenyetujuDokumen $penyetuju): View
    {
        $divisi = UserProfile::daftarDivisi();

        return view('portal.pbg.create', [
            'daftarDivisi' => $divisi,
            'kabag'        => $divisi->mapWithKeys(fn ($d) => [$d => $penyetuju->kepalaDepartemen($d)->label()]),
        ]);
    }

    public function store(Request $request, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $data = $request->validate([
            'tgl_pbg'             => ['required', 'date'],
            'departemen'          => ['required', Rule::in(UserProfile::daftarDivisi())],
            'unit_kerja'          => ['nullable', 'string', 'max:120'],
            'tgl_penggunaan'      => ['nullable', 'date'],
            'barang'              => ['required', 'array', 'min:1', 'max:' . self::MAKS_BARIS],
            'barang.*.kode'       => ['nullable', 'string', 'max:60'],
            'barang.*.nama'       => ['nullable', 'string', 'max:255'],
            'barang.*.jumlah'     => ['nullable', 'string', 'max:30'],
            'barang.*.satuan'     => ['nullable', 'string', 'max:30'],
            'barang.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'departemen.required' => 'Pilih departemen pemohon.',
            'departemen.in'       => 'Departemen itu tidak ada di data karyawan.',
            'barang.required'     => 'Isi minimal satu barang.',
        ]);

        // Baris kosong dari formulir dinamis tidak ikut disimpan.
        $data['barang'] = array_values(array_filter($data['barang'], fn ($b) => filled($b['nama'] ?? null)));

        if (empty($data['barang'])) {
            return back()->withInput()->withErrors(['barang' => 'Isi minimal satu nama barang.']);
        }

        $user = $request->user();

        $pbg = PbgRequest::create($data + [
            'nomor'   => PbgRequest::nomorBerikutnya(),
            'user_id' => $user->id,
            'nama'    => $user->name,
            'status'  => 'menunggu_kabag',
        ]);

        $catatan = $this->beritahuDanArsipkan($pbg, $arsip, $penyetuju, barusanDibuat: true);

        return redirect()->route('portal.pbg.show', $pbg)
            ->with('status', 'Permintaan ' . $pbg->nomor . ' terkirim. ' . $catatan);
    }

    public function show(Request $request, PbgRequest $pbg, PenyetujuDokumen $penyetuju): View
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $pbg, $penyetuju), 403);

        return view('portal.pbg.show', [
            'pbg'          => $pbg->load(['user', 'kepalaBagian', 'petugasGudang']),
            'bolehSetujui' => $this->bolehMenyetujui($user, $pbg, $penyetuju),
            'bolehProses'  => $this->orangGudang($user) && $pbg->status === 'menunggu_gudang',
            'tahap'        => $this->penyetujuTahapIni($pbg, $penyetuju),
        ]);
    }

    /** Keputusan kepala bagian. */
    public function putuskan(Request $request, PbgRequest $pbg, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->bolehMenyetujui($user, $pbg, $penyetuju), 403);

        $data = $request->validate([
            'keputusan'        => ['required', Rule::in(['setuju', 'tolak'])],
            'alasan_penolakan' => ['nullable', 'string', 'max:1000'],
        ]);

        $pbg->update($data['keputusan'] === 'tolak'
            ? ['status' => 'ditolak', 'alasan_penolakan' => $data['alasan_penolakan']]
            : ['status' => 'menunggu_gudang', 'kabag_oleh' => $user->id, 'kabag_pada' => now()]);

        $pbg->refresh();
        $catatan = $this->beritahuDanArsipkan($pbg, $arsip, $penyetuju, barusanDibuat: false);

        return back()->with('status', 'Keputusan tersimpan. ' . $catatan);
    }

    /** Gudang menyerahkan barangnya. */
    public function proses(Request $request, PbgRequest $pbg, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->orangGudang($user), 403);
        abort_unless(in_array($pbg->status, ['menunggu_gudang', 'selesai'], true), 400,
            'Permintaan ini belum disetujui kepala bagian.');

        $data = $request->validate([
            'kepala_gudang'  => ['nullable', 'string', 'max:120'],
            'catatan_gudang' => ['nullable', 'string', 'max:1000'],
        ]);

        $pbg->update($data + [
            'status'      => 'selesai',
            'gudang_oleh' => $user->id,
            'gudang_pada' => now(),
        ]);

        $pbg->refresh();
        $catatan = $this->beritahuDanArsipkan($pbg, $arsip, $penyetuju, barusanDibuat: false);

        return back()->with('status', 'Penyerahan barang tercatat. ' . $catatan);
    }

    public function pdf(Request $request, PbgRequest $pbg, PenyetujuDokumen $penyetuju)
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $pbg, $penyetuju), 403);

        return $this->buatPdf($pbg)->download($this->namaBerkas($pbg));
    }

    // ------------------------------------------------------------- bantuan

    private function beritahuDanArsipkan(PbgRequest $pbg, ArsipDivisi $arsip, PenyetujuDokumen $pd, bool $barusanDibuat): string
    {
        $catatan = [];

        $tahap = $this->penyetujuTahapIni($pbg, $pd);
        if ($tahap && $tahap->ada()) {
            Notification::send($tahap->orang, new PbgDiajukan($pbg, 'persetujuan'));
            if ($tahap->pengganti && $tahap->catatan) {
                $catatan[] = $tahap->catatan;
            }
        }

        // Formulir ini memang ditujukan ke gudang di divisi HR & GA: mereka
        // tahu sejak permintaan masuk, lalu diingatkan saat gilirannya.
        $gudang = $this->petugasGudang()
            ->where('id', '!=', $pbg->user_id)
            ->whereNotIn('id', $tahap?->orang->pluck('id')->all() ?? []);

        if ($gudang->isEmpty()) {
            $catatan[] = 'Belum ada akun gudang di divisi ' . self::DIVISI_GUDANG . ' yang bisa diberi tahu.';
        } elseif ($pbg->status === 'menunggu_gudang') {
            Notification::send($gudang, new PbgDiajukan($pbg, 'gudang'));
        } elseif ($barusanDibuat) {
            Notification::send($gudang, new PbgDiajukan($pbg, 'info'));
        }

        $pbg->user?->notify(new PbgDiajukan($pbg, $barusanDibuat ? 'tanda_terima' : 'hasil'));

        $item = $arsip->simpanPdf('pbg', $this->namaBerkas($pbg), $this->buatPdf($pbg)->output());
        $catatan[] = $item
            ? 'Salinan tersimpan di folder PBG divisi ' . self::DIVISI_GUDANG . '.'
            : 'Dokumen belum bisa diarsipkan karena divisi ' . self::DIVISI_GUDANG . ' belum punya akun.';

        return implode(' ', $catatan);
    }

    /**
     * Selain pemohon dan orang gudang, yang sudah membubuhkan persetujuan
     * tetap boleh membuka dokumennya — tanpa ini kepala bagian kehilangan
     * akses ke berkas yang baru saja ia setujui.
     */
    private function bolehMelihat(User $user, PbgRequest $pbg, PenyetujuDokumen $pd): bool
    {
        return $pbg->user_id === $user->id
            || $this->orangGudang($user)
            || $this->bolehMenyetujui($user, $pbg, $pd)
            || in_array($user->id, [$pbg->kabag_oleh, $pbg->gudang_oleh], true);
    }

    private function penyetujuTahapIni(PbgRequest $pbg, PenyetujuDokumen $pd): ?Penyetuju
    {
        return $pbg->status === 'menunggu_kabag' ? $pd->kepalaDepartemen($pbg->departemen) : null;
    }

    private function bolehMenyetujui(User $user, PbgRequest $pbg, PenyetujuDokumen $pd): bool
    {
        return (bool) $this->penyetujuTahapIni($pbg, $pd)?->orang->contains('id', $user->id);
    }

    private function idMenungguSaya(User $user): array
    {
        $pd = app(PenyetujuDokumen::class);

        return PbgRequest::where('status', 'menunggu_kabag')->get()
            ->filter(fn ($p) => $this->bolehMenyetujui($user, $p, $pd))
            ->pluck('id')->all();
    }

    /**
     * Yang diberi tahu urusan gudang. Divisi HR & GA berisi belasan orang,
     * sedangkan yang memegang gudang hanya sebagian; bila tidak ada jabatan
     * yang cocok, seluruh divisi diberi tahu agar permintaan tidak terlewat.
     */
    private function petugasGudang()
    {
        $sedivisi = User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_GUDANG))
            ->with('profile')->get();

        $cocok = $sedivisi->filter(
            fn ($u) => preg_match(self::KATA_GUDANG, $u->profile->position ?? '') === 1
        );

        return $cocok->isNotEmpty() ? $cocok->values() : $sedivisi;
    }

    private function buatPdf(PbgRequest $pbg)
    {
        return Pdf::loadView('portal.pbg.pdf', ['pbg' => $pbg])->setPaper('a4', 'landscape');
    }

    private function namaBerkas(PbgRequest $pbg): string
    {
        return str_replace('/', '-', $pbg->nomor) . ' ' . $pbg->departemen . '.pdf';
    }

    private function orangGudang(User $user): bool
    {
        return ($user->profile->division ?? null) === self::DIVISI_GUDANG
            || in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_HRD], true);
    }
}
