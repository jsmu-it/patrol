<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Services\LiveKitTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Meeting di Portal Kerja.
 *
 * Ruang rapatnya sama dengan yang dipakai dashboard admin — video, layar, dan
 * obrolan berjalan lewat server media LiveKit. Bedanya di sini setiap karyawan
 * boleh membuat dan mengikuti rapat, bukan hanya admin.
 *
 * Yang boleh mengakhiri rapat untuk semua orang hanya tuan rumahnya; peserta
 * lain cukup keluar dari ruangan.
 */
class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $saya = $request->user();

        // Rapat yang sudah usai tidak ikut ditampilkan agar daftarnya tetap
        // berisi yang masih bisa dimasuki.
        $meetings = Meeting::with('host:id,name')
            ->whereIn('status', ['scheduled', 'active'])
            ->orderByRaw("FIELD(status, 'active', 'scheduled')")
            ->orderByRaw('COALESCE(scheduled_at, created_at)')
            ->get();

        $riwayat = Meeting::with('host:id,name')
            ->where('status', 'ended')
            ->where('host_id', $saya->id)
            ->latest('ended_at')
            ->limit(5)
            ->get();

        return view('portal.meeting.index', compact('meetings', 'riwayat'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:500'],
            'scheduled_at' => ['nullable', 'date'],
            'password'     => ['nullable', 'string', 'min:4', 'max:32'],
        ], [
            'title.required' => 'Beri judul rapatnya.',
        ]);

        $saya = $request->user();

        $meeting = Meeting::create($data + [
            'slug'             => $this->slugUnik($data['title']),
            'room_name'        => 'jsmu-' . Str::lower(Str::random(12)),
            'host_id'          => $saya->id,
            'status'           => empty($data['scheduled_at']) ? 'active' : 'scheduled',
            'started_at'       => empty($data['scheduled_at']) ? now() : null,
            'max_participants' => 50,
        ]);

        // Rapat tanpa jadwal berarti dimulai sekarang — langsung masuk ruangan.
        return empty($data['scheduled_at'])
            ? redirect()->route('portal.meeting.room', $meeting)
            : redirect()->route('portal.meeting.index')
                ->with('status', 'Rapat "' . $meeting->title . '" dijadwalkan. Tautannya bisa dibagikan ke peserta.');
    }

    public function room(Request $request, Meeting $meeting): View
    {
        abort_if($meeting->status === 'ended', 410, 'Rapat ini sudah berakhir.');

        if ($meeting->status === 'scheduled') {
            $meeting->update(['status' => 'active', 'started_at' => now()]);
        }

        $saya      = $request->user();
        $tuanRumah = $meeting->host_id === $saya->id;

        $token = (new LiveKitTokenService())->generateToken(
            roomName: $meeting->room_name,
            participantName: $saya->name,
            isAdmin: $tuanRumah,
        );

        return view('admin.meetings.room', [
            'meeting'    => $meeting,
            'userName'   => $saya->name,
            'token'      => $token,
            'livekitUrl' => config('services.livekit.url'),
            'endUrl'     => route('portal.meeting.end', $meeting),
            'canEnd'     => $tuanRumah,
            'backUrl'    => route('portal.meeting.index'),
        ]);
    }

    /** Mengakhiri rapat untuk semua peserta — hanya tuan rumah. */
    public function end(Request $request, Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->host_id === $request->user()->id, 403);

        $meeting->update(['status' => 'ended', 'ended_at' => now()]);

        return redirect()->route('portal.meeting.index')->with('status', 'Rapat sudah diakhiri.');
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::slug(Str::limit($judul, 40, ''));
        $slug  = $dasar ?: 'rapat';

        for ($i = 2; Meeting::where('slug', $slug)->exists(); $i++) {
            $slug = $dasar . '-' . $i;
        }

        return $slug;
    }
}
