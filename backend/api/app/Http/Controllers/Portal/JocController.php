<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\JocCard;
use App\Models\Project;
use App\Models\User;
use App\Notifications\JocDikirim;
use App\Services\ArsipDivisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JocController extends Controller
{
    private const DIVISI_HSE = 'HSE';
    private const MAKS_FOTO  = 2;

    public function index(Request $request): View
    {
        $user = $request->user();

        // Anggota HSE melihat seluruh kartu; karyawan lain hanya miliknya.
        $query = JocCard::with(['user:id,name', 'project:id,name'])->latest();
        if (! $this->orangHse($user)) {
            $query->where('user_id', $user->id);
        }

        return view('portal.joc.index', [
            'kartu'  => $query->paginate(15),
            'hse'    => $this->orangHse($user),
        ]);
    }

    public function create(Request $request): View
    {
        return view('portal.joc.create', [
            'projects'  => Project::orderBy('name')->get(),
            'kategori'  => JocCard::daftarKategori(),
            'jenis'     => JocCard::daftarJenis(),
        ]);
    }

    public function store(Request $request, ArsipDivisi $arsip): RedirectResponse
    {
        $data = $request->validate([
            'tanggal'          => ['required', 'date'],
            'jam'              => ['required', 'date_format:H:i'],
            'lokasi'           => ['required', 'string', 'max:150'],
            'project_id'       => ['nullable', 'exists:projects,id'],
            'jenis_temuan'     => ['required', Rule::in(array_keys(JocCard::daftarJenis()))],
            'kategori'         => ['required', 'array', 'min:1'],
            'kategori.*'       => [Rule::in(array_keys(JocCard::daftarKategori()))],
            'kategori_lain'    => ['nullable', 'string', 'max:150'],
            'rincian_temuan'   => ['required', 'string', 'max:2000'],
            'rincian_tindakan' => ['required', 'string', 'max:2000'],
            'catatan'          => ['nullable', 'string', 'max:1000'],
            'ttd'              => ['nullable', 'string'],
            'foto'             => ['nullable', 'array', 'max:' . self::MAKS_FOTO],
            'foto.*'           => ['image', 'mimes:jpg,jpeg,png,heic,webp', 'max:8192'],
        ], [
            'kategori.required' => 'Pilih minimal satu kategori temuan.',
            'foto.max'          => 'Maksimal ' . self::MAKS_FOTO . ' foto bukti.',
            'foto.*.image'      => 'Berkas bukti harus berupa foto.',
            'foto.*.max'        => 'Ukuran tiap foto maksimal 8 MB.',
        ]);

        $user = $request->user();

        // Foto bukti disimpan di disk privat, dilayani lewat controller.
        $foto = [];
        foreach ((array) $request->file('foto', []) as $berkas) {
            $nama = uniqid('', true) . '.' . ($berkas->getClientOriginalExtension() ?: 'jpg');
            $berkas->storeAs('joc/' . $user->id, $nama, 'local');
            $foto[] = 'joc/' . $user->id . '/' . $nama;
        }
        unset($data['foto']);

        $kartu = JocCard::create($data + [
            'foto'    => $foto,
            'nomor'   => JocCard::nomorBerikutnya(),
            'user_id' => $user->id,
            'nama'    => $user->name,
            'status'  => 'baru',
        ]);

        // Divisi HSE diberi tahu; pengirim mendapat tanda terima.
        $hse = User::whereHas('profile', fn ($q) => $q->where('division', self::DIVISI_HSE))
            ->where('id', '!=', $user->id)->get();

        if ($hse->isNotEmpty()) {
            Notification::send($hse, new JocDikirim($kartu));
        }
        $user->notify(new JocDikirim($kartu, untukPengirim: true));

        // Salinan PDF diarsipkan ke folder JOC milik divisi HSE.
        $arsipTersimpan = $arsip->simpanPdf(
            'joc',
            str_replace('/', '-', $kartu->nomor) . ' ' . $kartu->lokasi . '.pdf',
            Pdf::loadView('portal.joc.pdf', ['kartu' => $kartu])->setPaper('a4')->output()
        );

        return redirect()->route('portal.joc.show', $kartu)
            ->with('status', 'Kartu ' . $kartu->nomor . ' terkirim.'
                . ($arsipTersimpan ? ' Salinan tersimpan di folder JOC divisi HSE.' : '')
                . ($hse->isEmpty()
                    ? ' Catatan: belum ada akun berdivisi HSE, jadi belum ada yang diberi tahu.'
                    : ' Divisi HSE sudah diberi tahu.'));
    }

    public function show(Request $request, JocCard $joc): View
    {
        $user = $request->user();
        abort_unless($joc->user_id === $user->id || $this->orangHse($user), 403);

        return view('portal.joc.show', ['kartu' => $joc->load(['user', 'project'])]);
    }

    /** Menyajikan foto bukti; hak aksesnya sama dengan kartunya. */
    public function foto(Request $request, JocCard $joc, int $index)
    {
        $user = $request->user();
        abort_unless($joc->user_id === $user->id || $this->orangHse($user), 403);

        $path = ($joc->foto ?? [])[$index] ?? null;
        abort_unless($path && \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

        return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($path));
    }

    /** Tanggapan HSE atas sebuah kartu. */
    public function tanggapi(Request $request, JocCard $joc): RedirectResponse
    {
        abort_unless($this->orangHse($request->user()), 403);

        $data = $request->validate([
            'status'        => ['required', Rule::in(array_keys(JocCard::daftarStatus()))],
            'tanggapan_hse' => ['nullable', 'string', 'max:2000'],
        ]);

        $joc->update($data + [
            'ditangani_oleh' => $request->user()->id,
            'ditangani_pada' => now(),
        ]);

        return back()->with('status', 'Tanggapan disimpan.');
    }

    private function orangHse(User $user): bool
    {
        return ($user->profile->division ?? null) === self::DIVISI_HSE
            || in_array($user->role, [User::ROLE_SUPERADMIN, User::ROLE_HRD], true);
    }
}
