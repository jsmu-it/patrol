<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\RptkRequest;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\RptkDikirim;
use App\Services\ArsipDivisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RptkController extends Controller
{
    private const DIVISI_HRGA = 'HR & GA';

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = RptkRequest::with('user:id,name')->latest();

        // Pemberi persetujuan dan HR & GA melihat semuanya.
        if (! $this->bolehMelihatSemua($user)) {
            $query->where('user_id', $user->id);
        }

        return view('portal.rptk.index', [
            'daftar'   => $query->paginate(15),
            'semua'    => $this->bolehMelihatSemua($user),
        ]);
    }

    public function create(): View
    {
        return view('portal.rptk.create', $this->pilihan());
    }

    /** Pilihan divisi dan lokasi kerja, diambil dari data yang sudah ada. */
    private function pilihan(): array
    {
        return [
            'daftarDivisi' => UserProfile::daftarDivisi(),
            'daftarLokasi' => Project::daftarLokasi(),
        ];
    }

    public function store(Request $request, ArsipDivisi $arsip): RedirectResponse
    {
        $data = $request->validate([
            'divisi'                => ['required', Rule::in(UserProfile::daftarDivisi())],
            'lokasi_kerja'          => ['required', Rule::in(Project::daftarLokasi())],
            'jumlah'                => ['required', 'integer', 'min:1', 'max:999'],
            'ref_dokumen'           => ['nullable', 'string', 'max:120'],
            'alasan'                => ['required', Rule::in(array_keys(RptkRequest::daftarAlasan()))],
            'alasan_lain'           => ['nullable', 'string', 'max:120'],
            'status_karyawan'       => ['required', Rule::in(array_keys(RptkRequest::daftarStatusKaryawan()))],
            'status_karyawan_lain'  => ['nullable', 'string', 'max:120'],
            'jabatan'               => ['required', 'string', 'max:120'],
            'uraian_tugas'          => ['required', 'string', 'max:4000'],
            'tinggi_badan'          => ['nullable', 'string', 'max:40'],
            'berat_badan'           => ['nullable', 'string', 'max:40'],
            'usia'                  => ['nullable', 'string', 'max:40'],
            'jenis_kelamin'         => ['required', Rule::in(array_keys(RptkRequest::daftarKelamin()))],
            'pendidikan_formal'     => ['nullable', 'string', 'max:120'],
            'pendidikan_non_formal' => ['nullable', 'string', 'max:120'],
            'keahlian_khusus'       => ['nullable', 'string', 'max:200'],
            'pengalaman'            => ['nullable', 'string', 'max:120'],
            'sumber'                => ['required', Rule::in(array_keys(RptkRequest::daftarSumber()))],
            'catatan'               => ['nullable', 'string', 'max:2000'],
            'tanggal_efektif'       => ['nullable', 'string', 'max:60'],
        ], [
            'divisi.required'       => 'Pilih divisi pemohon.',
            'divisi.in'             => 'Divisi itu tidak ada di data karyawan.',
            'lokasi_kerja.required' => 'Pilih lokasi kerja.',
            'lokasi_kerja.in'       => 'Lokasi kerja itu tidak ada di daftar project aktif.',
        ]);

        $user = $request->user();

        // Nomor dokumen mengikuti kode formulir baku, tidak diketik pemohon.
        $rptk = RptkRequest::create($data + [
            'no_tanggal_dokumen' => RptkRequest::nomorDokumen(),
            'nomor'   => RptkRequest::nomorBerikutnya(),
            'user_id' => $user->id,
            'nama'    => $user->name,
            'status'  => 'menunggu_hrga',
        ]);

        $catatan = $this->beritahuDanArsipkan($rptk, $arsip, barusanDibuat: true);

        return redirect()->route('portal.rptk.show', $rptk)
            ->with('status', 'Permintaan ' . $rptk->nomor . ' terkirim. ' . $catatan);
    }

    public function show(Request $request, RptkRequest $rptk): View
    {
        $user = $request->user();
        abort_unless($rptk->user_id === $user->id || $this->bolehMelihatSemua($user), 403);

        return view('portal.rptk.show', [
            'rptk'         => $rptk->load('user'),
            'bolehSetujui' => $this->bolehMenyetujui($user, $rptk),
        ]);
    }

    /** Menyetujui atau menolak, mengikuti tingkat persetujuan yang berlaku. */
    public function putuskan(Request $request, RptkRequest $rptk, ArsipDivisi $arsip): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->bolehMenyetujui($user, $rptk), 403);

        $data = $request->validate([
            'keputusan'        => ['required', Rule::in(['setuju', 'tolak'])],
            'alasan_penolakan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['keputusan'] === 'tolak') {
            $rptk->update([
                'status'           => 'ditolak',
                'alasan_penolakan' => $data['alasan_penolakan'],
            ]);
        } elseif ($rptk->status === 'menunggu_hrga') {
            $rptk->update([
                'status'    => 'menunggu_direktur',
                'hrga_oleh' => $user->id,
                'hrga_pada' => now(),
            ]);
        } else {
            $rptk->update([
                'status'        => 'disetujui',
                'direktur_oleh' => $user->id,
                'direktur_pada' => now(),
            ]);
        }

        $rptk->refresh();
        $catatan = $this->beritahuDanArsipkan($rptk, $arsip, barusanDibuat: false);

        return back()->with('status', 'Keputusan tersimpan. ' . $catatan);
    }

    public function pdf(Request $request, RptkRequest $rptk)
    {
        abort_unless($rptk->user_id === $request->user()->id || $this->bolehMelihatSemua($request->user()), 403);

        return $this->buatPdf($rptk)->download($this->namaBerkas($rptk));
    }

    // ------------------------------------------------------------- bantuan

    private function beritahuDanArsipkan(RptkRequest $rptk, ArsipDivisi $arsip, bool $barusanDibuat): string
    {
        $catatan = [];

        // Pemberi persetujuan pada tahap yang sedang berjalan
        $penyetuju = $this->penyetujuTahapIni($rptk);
        if ($penyetuju->isNotEmpty()) {
            Notification::send($penyetuju, new RptkDikirim($rptk, 'persetujuan'));
        } elseif (in_array($rptk->status, ['menunggu_hrga', 'menunggu_direktur'], true)) {
            $catatan[] = $rptk->status === 'menunggu_direktur'
                ? 'Belum ada akun Direktur, jadi belum ada yang bisa memberi persetujuan akhir.'
                : 'Belum ada akun HR & GA Manager, jadi belum ada yang diberi tahu.';
        }

        // HR & GA selalu tahu ada permintaan masuk
        if ($barusanDibuat) {
            $hrga = User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_HRGA))
                ->whereNotIn('id', $penyetuju->pluck('id'))->get();
            if ($hrga->isNotEmpty()) {
                Notification::send($hrga, new RptkDikirim($rptk, 'info'));
            }
        }

        // Pemohon diberi tahu hasilnya
        if (! $barusanDibuat) {
            $rptk->user?->notify(new RptkDikirim($rptk, 'hasil'));
        }

        // Arsip PDF ke folder RPTK divisi HR & GA
        $item = $arsip->simpanPdf('rptk', $this->namaBerkas($rptk), $this->buatPdf($rptk)->output());
        if (! $item) {
            $catatan[] = 'Dokumen belum bisa diarsipkan karena divisi HR & GA belum punya akun.';
        }

        return implode(' ', $catatan);
    }

    private function buatPdf(RptkRequest $rptk)
    {
        return Pdf::loadView('portal.rptk.pdf', ['rptk' => $rptk])->setPaper('a4');
    }

    private function namaBerkas(RptkRequest $rptk): string
    {
        return str_replace('/', '-', $rptk->nomor) . ' ' . $rptk->jabatan . '.pdf';
    }

    /** Siapa yang berwenang pada tahap persetujuan saat ini. */
    private function penyetujuTahapIni(RptkRequest $rptk)
    {
        if ($rptk->status === 'menunggu_hrga') {
            return User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_HRGA)
                ->where('position', 'like', '%Manager%'))->get();
        }

        if ($rptk->status === 'menunggu_direktur') {
            $direktur = User::whereHas('profile', fn ($q) => $q->where('position', 'like', '%Direktur%'))->get();

            // Belum ada akun Direktur — Superadmin menjadi penggantinya.
            return $direktur->isNotEmpty()
                ? $direktur
                : User::where('role', User::ROLE_SUPERADMIN)->get();
        }

        return collect();
    }

    private function bolehMenyetujui(User $user, RptkRequest $rptk): bool
    {
        return $this->penyetujuTahapIni($rptk)->contains('id', $user->id);
    }

    private function bolehMelihatSemua(User $user): bool
    {
        return ($user->profile->division ?? null) === self::DIVISI_HRGA
            || in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_HRD], true)
            || str_contains(strtolower($user->profile->position ?? ''), 'direktur');
    }
}
