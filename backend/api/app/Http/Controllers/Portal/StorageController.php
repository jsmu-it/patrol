<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PortalItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penyimpanan Data Portal JSMU.
 *
 * Setiap karyawan punya ruang sendiri. Berkas fisik disimpan di disk privat
 * `local` (storage/app/portal/{user_id}/), jadi tidak bisa diakses lewat URL
 * langsung — semua unduhan lewat controller ini yang memeriksa kepemilikan.
 */
class StorageController extends Controller
{
    /** Kuota per karyawan. */
    public const KUOTA_BYTE = 2 * 1024 * 1024 * 1024; // 2 GB

    private const MAKS_BERKAS = 102400; // 100 MB, mengikuti batas PHP & nginx

    /** Batas kedalaman folder yang ikut terbentuk saat mengunggah folder. */
    private const MAKS_KEDALAMAN = 12;

    // ---------------------------------------------------------------- baca

    public function index(Request $request): View
    {
        $user   = $request->user();
        $folder = $this->folderMilikUser($request, $request->integer('folder'));
        $cari   = trim((string) $request->input('q', ''));

        $query = PortalItem::milik($user->id)->aktif();

        if ($cari !== '') {
            // Pencarian menembus seluruh folder, seperti pencarian Drive.
            $query->where('name', 'like', '%' . $cari . '%');
        } else {
            $query->where('parent_id', $folder?->id);
        }

        $items = $query
            ->orderByRaw("FIELD(type, 'folder', 'file')")
            ->orderBy('name')
            ->get();

        return view('portal.storage.index', [
            'items'    => $items,
            'folder'   => $folder,
            'jejak'    => $folder ? $folder->jejak() : [],
            'cari'     => $cari,
            'terpakai' => $this->terpakai($user->id),
            'kuota'    => self::KUOTA_BYTE,
            // Untuk daftar tujuan "pindahkan"
            'semuaFolder' => PortalItem::milik($user->id)->aktif()
                ->where('type', PortalItem::TYPE_FOLDER)->orderBy('name')->get(),
            'folderDivisi' => $this->folderDibagikanPerDivisi($user->id),
            'daftarDivisi' => \App\Models\UserProfile::whereNotNull('division')
                ->where('division', '!=', '')->distinct()->orderBy('division')->pluck('division'),
        ]);
    }

    public function sampah(Request $request): View
    {
        $items = PortalItem::milik($request->user()->id)->diSampah()
            ->orderByDesc('trashed_at')->get();

        return view('portal.storage.sampah', compact('items'));
    }

    // --------------------------------------------------------------- tulis

    public function buatFolder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'parent_id'      => ['nullable', 'integer'],
            'share_mode'     => ['nullable', Rule::in(array_keys(PortalItem::modeBerbagi()))],
            'share_division' => ['nullable', 'string', 'max:100', 'required_if:share_mode,' . PortalItem::BAGI_DIVISI],
            'share_password' => ['nullable', 'string', 'min:4', 'max:64'],
        ], [
            'share_division.required_if' => 'Pilih divisi mana yang boleh melihat folder ini.',
        ]);

        $induk = $this->folderMilikUser($request, $data['parent_id'] ?? null);
        $mode  = $data['share_mode'] ?? PortalItem::BAGI_PRIVAT;

        PortalItem::create([
            'user_id'        => $request->user()->id,
            'parent_id'      => $induk?->id,
            'type'           => PortalItem::TYPE_FOLDER,
            'name'           => $this->namaUnik($request->user()->id, $induk?->id, $data['name']),
            'share_mode'     => $mode,
            'share_division' => $mode === PortalItem::BAGI_DIVISI ? ($data['share_division'] ?? null) : null,
            'share_token'    => $mode === PortalItem::BAGI_TAUTAN ? Str::random(40) : null,
            'share_password' => ($mode === PortalItem::BAGI_TAUTAN && ! empty($data['share_password']))
                                ? Hash::make($data['share_password']) : null,
        ]);

        return back()->with('status', 'Folder "' . $data['name'] . '" dibuat.');
    }

    public function unggah(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'files'     => ['required', 'array'],
            'files.*'   => ['required', 'file', 'max:' . self::MAKS_BERKAS],
            // Jalur relatif tiap berkas saat yang diunggah sebuah folder,
            // mis. "Laporan/2026/Januari/data.xlsx". Kosong berarti berkas
            // lepas yang langsung masuk folder yang sedang dibuka.
            'paths'     => ['nullable', 'array'],
            'paths.*'   => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $user  = $request->user();
        $induk = $this->folderMilikUser($request, $request->integer('parent_id') ?: null);

        $terpakai = $this->terpakai($user->id);
        $masuk    = collect($request->file('files'))->sum(fn ($f) => $f->getSize());

        if ($terpakai + $masuk > self::KUOTA_BYTE) {
            return back()->withErrors([
                'files' => 'Ruang penyimpanan tidak cukup. Terpakai '
                    . PortalItem::formatUkuran($terpakai) . ' dari '
                    . PortalItem::formatUkuran(self::KUOTA_BYTE) . '.',
            ]);
        }

        $jalur   = $request->input('paths', []);
        $jumlah  = 0;
        $folderBaru = 0;
        // Folder yang sudah disiapkan dalam satu kiriman, supaya berkas-berkas
        // di dalam folder yang sama tidak melahirkan folder kembar.
        $singgahan = [];

        foreach ($request->file('files') as $i => $berkas) {
            $namaAsli = $berkas->getClientOriginalName();
            $tujuan   = $induk;

            $bagian = $this->bagianJalur($jalur[$i] ?? null);
            if ($bagian) {
                $namaAsli = array_pop($bagian);
                $tujuan   = $this->folderDariJalur($user, $induk, $bagian, $singgahan, $folderBaru);
            }

            $simpanKe = 'portal/' . $user->id;
            $namaDisk = uniqid('', true) . '.' . ($berkas->getClientOriginalExtension() ?: 'bin');

            $berkas->storeAs($simpanKe, $namaDisk, 'local');

            PortalItem::create([
                'user_id'   => $user->id,
                'parent_id' => $tujuan?->id,
                'type'      => PortalItem::TYPE_FILE,
                'name'      => $this->namaUnik($user->id, $tujuan?->id, $namaAsli),
                'path'      => $simpanKe . '/' . $namaDisk,
                'mime_type' => $berkas->getClientMimeType(),
                'size'      => $berkas->getSize(),
            ]);
            $jumlah++;
        }

        $pesan = $jumlah . ' berkas diunggah.';
        if ($folderBaru > 0) {
            $pesan .= ' ' . $folderBaru . ' folder dibuat mengikuti susunan aslinya.';
        }

        if ($request->expectsJson() || $request->boolean('async')) {
            return response()->json(['ok' => true, 'pesan' => $pesan]);
        }

        return back()->with('status', $pesan);
    }

    public function unduh(Request $request, PortalItem $item): StreamedResponse
    {
        $this->pastikanMilikUser($request, $item);
        abort_if($item->isFolder() || ! $item->path, 404);
        abort_unless(Storage::disk('local')->exists($item->path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        return Storage::disk('local')->download($item->path, $item->name);
    }

    public function ubahNama(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        $item->update([
            'name' => $this->namaUnik($item->user_id, $item->parent_id, $data['name'], $item->id),
        ]);

        return back()->with('status', 'Nama diubah menjadi "' . $item->name . '".');
    }

    public function pindah(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);

        $data   = $request->validate(['parent_id' => ['nullable', 'integer']]);
        $tujuan = $this->folderMilikUser($request, $data['parent_id'] ?: null);

        // Folder tidak boleh dipindahkan ke dalam dirinya sendiri atau turunannya.
        if ($item->isFolder() && $tujuan) {
            foreach ($tujuan->jejak() as $leluhur) {
                if ($leluhur->id === $item->id) {
                    return back()->withErrors(['parent_id' => 'Folder tidak bisa dipindahkan ke dalam dirinya sendiri.']);
                }
            }
        }

        $item->update([
            'parent_id' => $tujuan?->id,
            'name'      => $this->namaUnik($item->user_id, $tujuan?->id, $item->name, $item->id),
        ]);

        return back()->with('status', '"' . $item->name . '" dipindahkan.');
    }

    /** Ubah pengaturan berbagi sebuah folder. */
    public function aturBerbagi(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);
        abort_unless($item->isFolder(), 400, 'Hanya folder yang bisa dibagikan.');

        $data = $request->validate([
            'share_mode'     => ['required', Rule::in(array_keys(PortalItem::modeBerbagi()))],
            'share_division' => ['nullable', 'string', 'max:100', 'required_if:share_mode,' . PortalItem::BAGI_DIVISI],
            'share_password' => ['nullable', 'string', 'min:4', 'max:64'],
            'hapus_sandi'    => ['nullable', 'boolean'],
        ], [
            'share_division.required_if' => 'Pilih divisi mana yang boleh melihat folder ini.',
        ]);

        $mode = $data['share_mode'];

        $item->share_mode     = $mode;
        $item->share_division = $mode === PortalItem::BAGI_DIVISI ? ($data['share_division'] ?? null) : null;

        if ($mode === PortalItem::BAGI_TAUTAN) {
            $item->share_token = $item->share_token ?: Str::random(40);

            if ($request->boolean('hapus_sandi')) {
                $item->share_password = null;
            } elseif (! empty($data['share_password'])) {
                $item->share_password = Hash::make($data['share_password']);
            }
        } else {
            // Mode selain tautan tidak memakai token maupun sandi.
            $item->share_token    = null;
            $item->share_password = null;
        }

        $item->save();

        return back()->with('status', 'Pengaturan berbagi "' . $item->name . '" diperbarui menjadi ' . $item->labelBerbagi() . '.');
    }

    /** Hapus = pindahkan ke tempat sampah, beserta seluruh isinya. */
    public function hapus(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);

        $this->tandaiSampah($item, now());

        return back()->with('status', '"' . $item->name . '" dipindahkan ke tempat sampah.');
    }

    public function pulihkan(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);

        // Bila folder induknya masih di sampah, kembalikan item ke akar
        // supaya tidak "hilang" di dalam folder yang tak terlihat.
        if ($item->parent_id && PortalItem::whereKey($item->parent_id)->whereNotNull('trashed_at')->exists()) {
            $item->parent_id = null;
        }

        $this->tandaiSampah($item, null);
        $item->save();

        return back()->with('status', '"' . $item->name . '" dipulihkan.');
    }

    public function hapusPermanen(Request $request, PortalItem $item): RedirectResponse
    {
        $this->pastikanMilikUser($request, $item);

        $nama = $item->name;
        $this->hapusBerkasFisik($item);
        $item->delete(); // turunannya ikut terhapus lewat cascade

        return back()->with('status', '"' . $nama . '" dihapus permanen.');
    }

    public function kosongkanSampah(Request $request): RedirectResponse
    {
        $items = PortalItem::milik($request->user()->id)->diSampah()->get();

        foreach ($items as $item) {
            $this->hapusBerkasFisik($item);
        }
        PortalItem::milik($request->user()->id)->diSampah()->delete();

        return back()->with('status', 'Tempat sampah dikosongkan.');
    }

    // ------------------------------------------------------------- bantuan

    /**
     * Folder yang dibagikan, dikelompokkan menurut divisi pemiliknya.
     * Dipakai sidebar agar karyawan bisa menemukan folder divisi tanpa
     * harus dikirimi tautan.
     */
    private function folderDibagikanPerDivisi(int $userIdSaya)
    {
        $saya = User::find($userIdSaya);

        return PortalItem::dibagikanInternal()
            ->with(['user:id,name', 'user.profile:id,user_id,division'])
            ->orderBy('name')
            ->get()
            // Folder bermode "divisi" hanya muncul bagi anggota divisi itu.
            ->filter(fn ($f) => $f->bolehDibukaOleh($saya))
            // Arsip divisi tidak punya pemilik perorangan, jadi pengelompokan
            // memakai divisi item itu sendiri.
            ->groupBy(fn ($f) => $f->divisi() ?? 'Tanpa Divisi')
            ->sortKeys();
    }

    private function terpakai(int $userId): int
    {
        return (int) PortalItem::milik($userId)->aktif()
            ->where('type', PortalItem::TYPE_FILE)->sum('size');
    }

    /** Memastikan folder yang diminta memang milik pengguna yang login. */
    private function folderMilikUser(Request $request, ?int $id): ?PortalItem
    {
        if (! $id) {
            return null;
        }

        return PortalItem::milik($request->user()->id)->aktif()
            ->where('type', PortalItem::TYPE_FOLDER)
            ->findOrFail($id);
    }

    private function pastikanMilikUser(Request $request, PortalItem $item): void
    {
        abort_unless($item->user_id === $request->user()->id, 403);
    }

    /** Menambahkan " (2)", " (3)" bila nama sudah dipakai di folder yang sama. */
    /**
     * Memecah jalur relatif kiriman menjadi bagian yang aman dipakai.
     * Nama seperti "..", jalur kosong, dan pemisah ganda dibuang supaya berkas
     * tidak bisa ditanam di luar ruang penyimpanan penggunanya.
     *
     * @return string[] bagian folder diikuti nama berkas di posisi terakhir
     */
    private function bagianJalur(?string $jalur): array
    {
        if (! $jalur) {
            return [];
        }

        $bagian = collect(preg_split('#[\\\\/]+#', $jalur))
            ->map(fn ($b) => trim($b))
            ->filter(fn ($b) => $b !== '' && $b !== '.')
            ->values();

        // Jalur yang mengandung ".." tidak ditafsirkan ulang, melainkan
        // diabaikan seluruhnya: berkasnya masuk ke folder yang sedang dibuka.
        // Menebak maksudnya justru membuat folder yang tidak diminta siapa pun.
        if ($bagian->contains('..')) {
            return [];
        }

        $bagian = $bagian->take(self::MAKS_KEDALAMAN + 1)->all();   // +1 untuk nama berkasnya

        return count($bagian) > 1 ? $bagian : [];
    }

    /**
     * Menyiapkan rantai folder sesuai jalur, memakai folder yang sudah ada bila
     * namanya cocok dan membuat yang belum ada.
     *
     * @param  string[]  $bagian
     * @param  array<string, PortalItem>  $singgahan
     */
    private function folderDariJalur(User $user, ?PortalItem $induk, array $bagian, array &$singgahan, int &$folderBaru): ?PortalItem
    {
        $sekarang = $induk;

        foreach ($bagian as $nama) {
            $kunci = ($sekarang?->id ?? 0) . '/' . $nama;

            if (isset($singgahan[$kunci])) {
                $sekarang = $singgahan[$kunci];
                continue;
            }

            $folder = PortalItem::milik($user->id)->aktif()
                ->where('type', PortalItem::TYPE_FOLDER)
                ->where('parent_id', $sekarang?->id)
                ->where('name', $nama)
                ->first();

            if (! $folder) {
                $folder = PortalItem::create([
                    'user_id'   => $user->id,
                    'parent_id' => $sekarang?->id,
                    'type'      => PortalItem::TYPE_FOLDER,
                    'name'      => $nama,
                ]);
                $folderBaru++;
            }

            $singgahan[$kunci] = $folder;
            $sekarang = $folder;
        }

        return $sekarang;
    }

    private function namaUnik(int $userId, ?int $parentId, string $nama, ?int $kecualiId = null): string
    {
        $ada = fn (string $kandidat) => PortalItem::milik($userId)
            ->where('parent_id', $parentId)
            ->where('name', $kandidat)
            ->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))
            ->exists();

        if (! $ada($nama)) {
            return $nama;
        }

        $ext  = pathinfo($nama, PATHINFO_EXTENSION);
        $dasar = $ext ? pathinfo($nama, PATHINFO_FILENAME) : $nama;

        for ($i = 2; $i < 500; $i++) {
            $kandidat = $dasar . ' (' . $i . ')' . ($ext ? '.' . $ext : '');
            if (! $ada($kandidat)) {
                return $kandidat;
            }
        }

        return $dasar . ' (' . time() . ')' . ($ext ? '.' . $ext : '');
    }

    /** Menandai item dan seluruh turunannya sebagai sampah / kembali aktif. */
    private function tandaiSampah(PortalItem $item, $waktu): void
    {
        $item->trashed_at = $waktu;
        $item->save();

        foreach ($item->children as $anak) {
            $this->tandaiSampah($anak, $waktu);
        }
    }

    private function hapusBerkasFisik(PortalItem $item): void
    {
        if ($item->type === PortalItem::TYPE_FILE && $item->path) {
            Storage::disk('local')->delete($item->path);
        }

        foreach ($item->children as $anak) {
            $this->hapusBerkasFisik($anak);
        }
    }
}
