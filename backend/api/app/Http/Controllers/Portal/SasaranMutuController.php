<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SasaranMutu;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\SasaranMutuDikirim;
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
 * Laporan Pencapaian Sasaran Mutu (MR/FM-07-02a).
 *
 * Setelah dikirim, laporan menempuh dua tahap persetujuan mengikuti kolom
 * tanda tangan pada formulir: diperiksa kepala departemen divisi pembuat, lalu
 * disetujui Direktur. Salinan PDF-nya mengendap di folder Sasaran Mutu milik
 * divisi DOC.CONTROL dan diperbarui setiap status berubah, sehingga arsip yang
 * tersimpan selalu memuat tanda tangan terakhir.
 */
class SasaranMutuController extends Controller
{
    private const DIVISI_DOC = 'DOC.CONTROL';
    private const MAKS_BARIS = 20;

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = SasaranMutu::with('user:id,name')->latest();

        // Pemegang dokumen melihat semuanya; karyawan lain hanya laporannya
        // sendiri ditambah yang sedang menunggu keputusannya.
        if (! $this->pemegangDokumen($user)) {
            $query->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('id', $this->idMenungguSaya($user)));
        }

        return view('portal.sasaran-mutu.index', [
            'daftar' => $query->paginate(15),
            'semua'  => $this->pemegangDokumen($user),
        ]);
    }

    public function create(Request $request, PenyetujuDokumen $penyetuju): View
    {
        $divisi = UserProfile::daftarDivisi();

        // Pembuat perlu tahu ke siapa laporannya akan mengalir sebelum dikirim,
        // termasuk ketika divisinya belum punya akun kepala departemen.
        $pemeriksa = $divisi->mapWithKeys(fn ($d) => [
            $d => $penyetuju->kepalaDepartemen($d)->label(),
        ]);

        return view('portal.sasaran-mutu.create', [
            'daftarDivisi' => $divisi,
            'pemeriksa'    => $pemeriksa,
            'direktur'     => $penyetuju->direktur()->label(),
        ]);
    }

    public function store(Request $request, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $data = $request->validate([
            'bulan'                   => ['required', 'date_format:Y-m'],
            'departemen'              => ['required', Rule::in(UserProfile::daftarDivisi())],
            'sub_departemen'          => ['nullable', 'string', 'max:120'],
            'tanggal'                 => ['required', 'date'],

            'sasaran'                 => ['required', 'array', 'min:1', 'max:' . self::MAKS_BARIS],
            'sasaran.*.sasaran'       => ['nullable', 'string', 'max:255'],
            'sasaran.*.ukuran'        => ['nullable', 'string', 'max:255'],
            'sasaran.*.target_ukuran' => ['nullable', 'string', 'max:60'],
            'sasaran.*.target_unit'   => ['nullable', 'string', 'max:40'],
            'sasaran.*.realisasi'     => ['nullable', 'string', 'max:60'],
            'sasaran.*.pencapaian'    => ['nullable', 'string', 'max:40'],

            'pelaksanaan'             => ['required', 'string', 'max:4000'],
            'penyebab'                => ['nullable', 'string', 'max:4000'],

            'tindak_lanjut'               => ['nullable', 'array', 'max:' . self::MAKS_BARIS],
            'tindak_lanjut.*.kegiatan'    => ['nullable', 'string', 'max:255'],
            'tindak_lanjut.*.pic'         => ['nullable', 'string', 'max:120'],
            'tindak_lanjut.*.batas_waktu' => ['nullable', 'string', 'max:60'],
            'tindak_lanjut.*.status'      => ['nullable', Rule::in(array_keys(SasaranMutu::daftarStatusTindakLanjut()))],

            'jabatan_pembuat'         => ['nullable', 'string', 'max:120'],
        ], [
            'departemen.required' => 'Pilih departemen pembuat laporan.',
            'departemen.in'       => 'Departemen itu tidak ada di data karyawan.',
            'bulan.required'      => 'Pilih bulan laporan.',
            'sasaran.required'    => 'Isi minimal satu sasaran strategi.',
        ]);

        // Baris kosong yang ikut terkirim dari formulir dinamis tidak disimpan.
        $data['sasaran'] = array_values(array_filter(
            $data['sasaran'],
            fn ($b) => filled($b['sasaran'] ?? null)
        ));
        $data['tindak_lanjut'] = array_values(array_filter(
            $data['tindak_lanjut'] ?? [],
            fn ($b) => filled($b['kegiatan'] ?? null)
        ));

        if (empty($data['sasaran'])) {
            return back()->withInput()->withErrors(['sasaran' => 'Isi minimal satu sasaran strategi.']);
        }

        $user = $request->user();

        $laporan = SasaranMutu::create($data + [
            'nomor'           => SasaranMutu::nomorBerikutnya(),
            'user_id'         => $user->id,
            'nama'            => $user->name,
            'jabatan_pembuat' => $data['jabatan_pembuat'] ?? ($user->profile->position ?? null),
            'status'          => 'menunggu_manager',
        ]);

        $catatan = $this->beritahuDanArsipkan($laporan, $arsip, $penyetuju, barusanDibuat: true);

        return redirect()->route('portal.sasaran-mutu.show', $laporan)
            ->with('status', 'Laporan ' . $laporan->nomor . ' terkirim. ' . $catatan);
    }

    /** Memeriksa (kepala departemen) atau menyetujui (Direktur). */
    public function putuskan(Request $request, SasaranMutu $sasaranMutu, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->bolehMenyetujui($user, $sasaranMutu, $penyetuju), 403);

        $data = $request->validate([
            'keputusan'        => ['required', Rule::in(['setuju', 'tolak'])],
            'alasan_penolakan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['keputusan'] === 'tolak') {
            $sasaranMutu->update([
                'status'           => 'ditolak',
                'alasan_penolakan' => $data['alasan_penolakan'],
            ]);
        } elseif ($sasaranMutu->status === 'menunggu_manager') {
            $sasaranMutu->update([
                'status'       => 'menunggu_direktur',
                'manager_oleh' => $user->id,
                'manager_pada' => now(),
            ]);
        } else {
            $sasaranMutu->update([
                'status'        => 'disetujui',
                'direktur_oleh' => $user->id,
                'direktur_pada' => now(),
            ]);
        }

        $sasaranMutu->refresh();
        $catatan = $this->beritahuDanArsipkan($sasaranMutu, $arsip, $penyetuju, barusanDibuat: false);

        return back()->with('status', 'Keputusan tersimpan. ' . $catatan);
    }

    public function show(Request $request, SasaranMutu $sasaranMutu, PenyetujuDokumen $penyetuju): View
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $sasaranMutu, $penyetuju), 403);

        return view('portal.sasaran-mutu.show', [
            'laporan'      => $sasaranMutu->load(['user', 'pemeriksa', 'penyetuju']),
            'bolehSetujui' => $this->bolehMenyetujui($user, $sasaranMutu, $penyetuju),
            'tahap'        => $this->penyetujuTahapIni($sasaranMutu, $penyetuju),
        ]);
    }

    public function pdf(Request $request, SasaranMutu $sasaranMutu, PenyetujuDokumen $penyetuju)
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $sasaranMutu, $penyetuju), 403);

        return $this->buatPdf($sasaranMutu)->download($this->namaBerkas($sasaranMutu));
    }

    // ------------------------------------------------------------- bantuan

    /**
     * Memberi tahu pihak yang berkepentingan lalu memperbarui arsip PDF.
     * Mengembalikan catatan yang layak ditampilkan kepada pengirim.
     */
    private function beritahuDanArsipkan(SasaranMutu $laporan, ArsipDivisi $arsip, PenyetujuDokumen $pd, bool $barusanDibuat): string
    {
        $catatan = [];

        // Pemberi keputusan pada tahap yang sedang berjalan
        $tahap = $this->penyetujuTahapIni($laporan, $pd);
        if ($tahap && $tahap->ada()) {
            Notification::send($tahap->orang, new SasaranMutuDikirim($laporan, 'persetujuan'));
            if ($tahap->pengganti && $tahap->catatan) {
                $catatan[] = $tahap->catatan;
            }
        }

        // Pemegang dokumen mutu selalu tahu ada laporan masuk
        if ($barusanDibuat) {
            $doc = User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_DOC))
                ->where('id', '!=', $laporan->user_id)
                ->whereNotIn('id', $tahap?->orang->pluck('id') ?? [])
                ->get();

            if ($doc->isNotEmpty()) {
                Notification::send($doc, new SasaranMutuDikirim($laporan, 'info'));
            } else {
                $catatan[] = 'Belum ada akun berdivisi DOC.CONTROL yang bisa diberi tahu.';
            }

            $laporan->user?->notify(new SasaranMutuDikirim($laporan, 'tanda_terima'));
        } else {
            $laporan->user?->notify(new SasaranMutuDikirim($laporan, 'hasil'));
        }

        // Arsip diperbarui di tempat, jadi tanda tangan terbaru ikut tersimpan.
        $item = $arsip->simpanPdf('sasaran_mutu', $this->namaBerkas($laporan), $this->buatPdf($laporan)->output());
        $catatan[] = $item
            ? 'Salinan tersimpan di folder Sasaran Mutu divisi DOC.CONTROL.'
            : 'Dokumen belum bisa diarsipkan karena divisi DOC.CONTROL belum punya akun.';

        return implode(' ', $catatan);
    }

    /** Penanda tangan tetap boleh membuka dokumen yang sudah ia putuskan. */
    private function bolehMelihat(User $user, SasaranMutu $l, PenyetujuDokumen $pd): bool
    {
        return $l->user_id === $user->id
            || $this->pemegangDokumen($user)
            || $this->bolehMenyetujui($user, $l, $pd)
            || in_array($user->id, [$l->manager_oleh, $l->direktur_oleh], true);
    }

    /** Siapa yang berwenang pada tahap saat ini, null bila sudah selesai. */
    private function penyetujuTahapIni(SasaranMutu $laporan, PenyetujuDokumen $pd): ?Penyetuju
    {
        return match ($laporan->status) {
            'menunggu_manager'  => $pd->kepalaDepartemen($laporan->departemen),
            'menunggu_direktur' => $pd->direktur(),
            default             => null,
        };
    }

    private function bolehMenyetujui(User $user, SasaranMutu $laporan, PenyetujuDokumen $pd): bool
    {
        return (bool) $this->penyetujuTahapIni($laporan, $pd)?->orang->contains('id', $user->id);
    }

    /** Id laporan yang menunggu keputusan pengguna ini, untuk penyaringan daftar. */
    private function idMenungguSaya(User $user): array
    {
        $pd = app(PenyetujuDokumen::class);

        return SasaranMutu::whereIn('status', ['menunggu_manager', 'menunggu_direktur'])
            ->get()
            ->filter(fn ($l) => $this->bolehMenyetujui($user, $l, $pd))
            ->pluck('id')->all();
    }

    private function buatPdf(SasaranMutu $laporan)
    {
        return Pdf::loadView('portal.sasaran-mutu.pdf', ['laporan' => $laporan])
            ->setPaper('a4', 'landscape');
    }

    private function namaBerkas(SasaranMutu $laporan): string
    {
        return str_replace('/', '-', $laporan->nomor) . ' ' . $laporan->departemen
            . ' ' . $laporan->labelBulan() . '.pdf';
    }

    private function pemegangDokumen(User $user): bool
    {
        return ($user->profile->division ?? null) === self::DIVISI_DOC
            || in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_HRD], true);
    }
}
