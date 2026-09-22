<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PermintaanKendaraan;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\KendaraanDiminta;
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
 * Form Permintaan Kendaraan.
 *
 * Mengikuti alur tanda tangan pada formulir cetak: pemohon mengisi, atasan
 * langsung menyetujui, lalu bagian GA menetapkan driver dan nomor polisi.
 * Salinan PDF-nya disimpan di folder Permintaan Kendaraan milik divisi GA dan
 * diperbarui setiap status berubah.
 */
class KendaraanController extends Controller
{
    private const DIVISI_GA = 'HR & GA';

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = PermintaanKendaraan::with('user:id,name')->latest();

        // Bagian GA melihat semuanya; karyawan lain hanya permintaannya sendiri
        // ditambah yang sedang menunggu persetujuannya.
        if (! $this->orangGa($user)) {
            $query->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('id', $this->idMenungguSaya($user)));
        }

        return view('portal.kendaraan.index', [
            'daftar' => $query->paginate(15),
            'semua'  => $this->orangGa($user),
        ]);
    }

    public function create(Request $request, PenyetujuDokumen $penyetuju): View
    {
        $divisi = UserProfile::daftarDivisi();

        return view('portal.kendaraan.create', [
            'daftarDivisi' => $divisi,
            'atasan'       => $divisi->mapWithKeys(fn ($d) => [$d => $penyetuju->kepalaDepartemen($d)->label()]),
        ]);
    }

    public function store(Request $request, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $data = $request->validate([
            'bagian'        => ['required', Rule::in(UserProfile::daftarDivisi())],
            'tanggal_pakai' => ['required', 'date'],
            'jam'           => ['required', 'date_format:H:i'],
            'keperluan'     => ['required', 'string', 'max:2000'],
        ], [
            'bagian.required' => 'Pilih bagian pemohon.',
            'bagian.in'       => 'Bagian itu tidak ada di data karyawan.',
        ]);

        $user = $request->user();

        $permintaan = PermintaanKendaraan::create($data + [
            'nomor'   => PermintaanKendaraan::nomorBerikutnya(),
            'user_id' => $user->id,
            'nama'    => $user->name,
            'status'  => 'menunggu_atasan',
        ]);

        $catatan = $this->beritahuDanArsipkan($permintaan, $arsip, $penyetuju, barusanDibuat: true);

        return redirect()->route('portal.kendaraan.show', $permintaan)
            ->with('status', 'Permintaan ' . $permintaan->nomor . ' terkirim. ' . $catatan);
    }

    public function show(Request $request, PermintaanKendaraan $kendaraan, PenyetujuDokumen $penyetuju): View
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $kendaraan, $penyetuju), 403);

        return view('portal.kendaraan.show', [
            'permintaan'   => $kendaraan->load(['user', 'atasan', 'petugasGa']),
            'bolehSetujui' => $this->bolehMenyetujui($user, $kendaraan, $penyetuju),
            'bolehTindak'  => $this->orangGa($user) && $kendaraan->status === 'menunggu_ga',
            'tahap'        => $this->penyetujuTahapIni($kendaraan, $penyetuju),
        ]);
    }

    /** Keputusan atasan langsung. */
    public function putuskan(Request $request, PermintaanKendaraan $kendaraan, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->bolehMenyetujui($user, $kendaraan, $penyetuju), 403);

        $data = $request->validate([
            'keputusan'        => ['required', Rule::in(['setuju', 'tolak'])],
            'alasan_penolakan' => ['nullable', 'string', 'max:1000'],
        ]);

        $kendaraan->update($data['keputusan'] === 'tolak'
            ? ['status' => 'ditolak', 'alasan_penolakan' => $data['alasan_penolakan']]
            : ['status' => 'menunggu_ga', 'atasan_oleh' => $user->id, 'atasan_pada' => now()]);

        $kendaraan->refresh();
        $catatan = $this->beritahuDanArsipkan($kendaraan, $arsip, $penyetuju, barusanDibuat: false);

        return back()->with('status', 'Keputusan tersimpan. ' . $catatan);
    }

    /** Catatan GA: menetapkan driver dan nomor polisi. */
    public function tindak(Request $request, PermintaanKendaraan $kendaraan, ArsipDivisi $arsip, PenyetujuDokumen $penyetuju): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->orangGa($user), 403);
        abort_unless(in_array($kendaraan->status, ['menunggu_ga', 'siap'], true), 400,
            'Permintaan ini belum disetujui atasan langsung.');

        $data = $request->validate([
            'driver'     => ['required', 'string', 'max:120'],
            'no_polisi'  => ['required', 'string', 'max:40'],
            'catatan_ga' => ['nullable', 'string', 'max:1000'],
        ], [
            'driver.required'    => 'Isi nama driver.',
            'no_polisi.required' => 'Isi nomor polisi kendaraan.',
        ]);

        $kendaraan->update($data + [
            'status'  => 'siap',
            'ga_oleh' => $user->id,
            'ga_pada' => now(),
        ]);

        $kendaraan->refresh();
        $catatan = $this->beritahuDanArsipkan($kendaraan, $arsip, $penyetuju, barusanDibuat: false);

        return back()->with('status', 'Driver dan nomor polisi tersimpan. ' . $catatan);
    }

    public function pdf(Request $request, PermintaanKendaraan $kendaraan, PenyetujuDokumen $penyetuju)
    {
        $user = $request->user();
        abort_unless($this->bolehMelihat($user, $kendaraan, $penyetuju), 403);

        return $this->buatPdf($kendaraan)->download($this->namaBerkas($kendaraan));
    }

    // ------------------------------------------------------------- bantuan

    private function beritahuDanArsipkan(PermintaanKendaraan $p, ArsipDivisi $arsip, PenyetujuDokumen $pd, bool $barusanDibuat): string
    {
        $catatan = [];

        // Atasan langsung, bila masih menunggu persetujuannya
        $tahap = $this->penyetujuTahapIni($p, $pd);
        if ($tahap && $tahap->ada()) {
            Notification::send($tahap->orang, new KendaraanDiminta($p, 'persetujuan'));
            if ($tahap->pengganti && $tahap->catatan) {
                $catatan[] = $tahap->catatan;
            }
        }

        // Bagian GA baru diberi tahu ketika gilirannya tiba — sebelum atasan
        // menyetujui, permintaan belum ada gunanya buat mereka.
        if ($p->status === 'menunggu_ga') {
            $ga = $this->petugasGa()->where('id', '!=', $p->user_id);

            if ($ga->isNotEmpty()) {
                Notification::send($ga, new KendaraanDiminta($p, 'ga'));
            } else {
                $catatan[] = 'Belum ada akun berdivisi ' . self::DIVISI_GA . ' yang bisa menetapkan driver.';
            }
        }

        // Pemohon: tanda terima saat kirim, kabar hasil setiap ada perubahan
        $p->user?->notify(new KendaraanDiminta($p, $barusanDibuat ? 'tanda_terima' : 'hasil'));

        $item = $arsip->simpanPdf('kendaraan', $this->namaBerkas($p), $this->buatPdf($p)->output());
        $catatan[] = $item
            ? 'Salinan tersimpan di folder Permintaan Kendaraan divisi ' . self::DIVISI_GA . '.'
            : 'Dokumen belum bisa diarsipkan karena divisi ' . self::DIVISI_GA . ' belum punya akun.';

        return implode(' ', $catatan);
    }

    /** Penanda tangan tetap boleh membuka dokumen yang sudah ia putuskan. */
    private function bolehMelihat(User $user, PermintaanKendaraan $p, PenyetujuDokumen $pd): bool
    {
        return $p->user_id === $user->id
            || $this->orangGa($user)
            || $this->bolehMenyetujui($user, $p, $pd)
            || in_array($user->id, [$p->atasan_oleh, $p->ga_oleh], true);
    }

    /** Yang berwenang pada tahap persetujuan saat ini, null bila sudah lewat. */
    private function penyetujuTahapIni(PermintaanKendaraan $p, PenyetujuDokumen $pd): ?Penyetuju
    {
        return $p->status === 'menunggu_atasan' ? $pd->kepalaDepartemen($p->bagian) : null;
    }

    private function bolehMenyetujui(User $user, PermintaanKendaraan $p, PenyetujuDokumen $pd): bool
    {
        return (bool) $this->penyetujuTahapIni($p, $pd)?->orang->contains('id', $user->id);
    }

    private function idMenungguSaya(User $user): array
    {
        $pd = app(PenyetujuDokumen::class);

        return PermintaanKendaraan::where('status', 'menunggu_atasan')->get()
            ->filter(fn ($p) => $this->bolehMenyetujui($user, $p, $pd))
            ->pluck('id')->all();
    }

    private function buatPdf(PermintaanKendaraan $p)
    {
        return Pdf::loadView('portal.kendaraan.pdf', ['permintaan' => $p])->setPaper('a5');
    }

    private function namaBerkas(PermintaanKendaraan $p): string
    {
        return str_replace('/', '-', $p->nomor) . ' ' . $p->nama . '.pdf';
    }

    /**
     * Yang diberi tahu saat giliran GA tiba. Divisi HR & GA berisi belasan
     * orang — payroll, rekrutmen, driver — sedangkan yang mengurus kendaraan
     * hanya bagian GA-nya, jadi pemberitahuan disempitkan ke jabatan yang
     * memuat kata "GA". Bila tidak ada yang cocok, seluruh divisi diberi tahu
     * supaya permintaan tidak terlewat. Haknya sendiri tetap sedivisi.
     */
    private function petugasGa()
    {
        $sedivisi = User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_GA))
            ->with('profile')->get();

        $bagianGa = $sedivisi->filter(
            fn ($u) => preg_match('/\b(ga|hrga)\b/i', $u->profile->position ?? '') === 1
        );

        return $bagianGa->isNotEmpty() ? $bagianGa->values() : $sedivisi;
    }

    private function orangGa(User $user): bool
    {
        return ($user->profile->division ?? null) === self::DIVISI_GA
            || in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_HRD], true);
    }
}
