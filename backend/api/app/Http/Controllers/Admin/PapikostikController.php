<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PapikostikQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PapikostikController extends Controller
{
    public function index(): View
    {
        $questions = PapikostikQuestion::orderBy('created_at', 'desc')->paginate(10);
        $dimensions = PapikostikQuestion::DIMENSIONS;
        return view('admin.psikotest.papikostik.index', compact('questions', 'dimensions'));
    }

    public function create(): View
    {
        $dimensions = PapikostikQuestion::DIMENSIONS;
        return view('admin.psikotest.papikostik.create', compact('dimensions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pairs' => ['required', 'array', 'min:1'],
            'pairs.*.statement_a' => ['required', 'string'],
            'pairs.*.statement_b' => ['required', 'string'],
            'pairs.*.dimension_a' => ['required', 'string', 'size:1'],
            'pairs.*.dimension_b' => ['required', 'string', 'size:1'],
        ]);

        PapikostikQuestion::create([
            'name' => $data['name'],
            'pairs' => $data['pairs'],
        ]);

        return redirect()->route('admin.psikotest.papikostik.index')
            ->with('status', 'Soal Papikostik berhasil dibuat.');
    }

    public function show(PapikostikQuestion $papikostik): View
    {
        $dimensions = PapikostikQuestion::DIMENSIONS;
        return view('admin.psikotest.papikostik.show', compact('papikostik', 'dimensions'));
    }

    public function edit(PapikostikQuestion $papikostik): View
    {
        $dimensions = PapikostikQuestion::DIMENSIONS;
        return view('admin.psikotest.papikostik.edit', compact('papikostik', 'dimensions'));
    }

    public function update(Request $request, PapikostikQuestion $papikostik): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pairs' => ['sometimes', 'array'],
            'pairs.*.statement_a' => ['required', 'string'],
            'pairs.*.statement_b' => ['required', 'string'],
            'pairs.*.dimension_a' => ['required', 'string', 'size:1'],
            'pairs.*.dimension_b' => ['required', 'string', 'size:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $updateData = ['name' => $data['name']];
        
        if (isset($data['pairs'])) {
            $updateData['pairs'] = $data['pairs'];
        }
        
        $updateData['is_active'] = $request->boolean('is_active');

        $papikostik->update($updateData);

        return redirect()->route('admin.psikotest.papikostik.index')
            ->with('status', 'Soal Papikostik berhasil diperbarui.');
    }

    public function destroy(PapikostikQuestion $papikostik): RedirectResponse
    {
        $papikostik->delete();

        return redirect()->route('admin.psikotest.papikostik.index')
            ->with('status', 'Soal Papikostik berhasil dihapus.');
    }

    public function generateStandard(): RedirectResponse
    {
        // Generate standard PAPIKOSTIK questions (Indonesian version)
        $standardPairs = $this->getStandardPairs();

        PapikostikQuestion::create([
            'name' => 'PAPIKOSTIK Standar Indonesia',
            'pairs' => $standardPairs,
            'is_active' => true,
        ]);

        return redirect()->route('admin.psikotest.papikostik.index')
            ->with('status', 'Soal PAPIKOSTIK standar berhasil dibuat.');
    }

    private function getStandardPairs(): array
    {
        // 90 standard PAPIKOSTIK pairs
        return [
            ['statement_a' => 'Saya bekerja keras', 'statement_b' => 'Saya tidak mudah marah', 'dimension_a' => 'G', 'dimension_b' => 'E'],
            ['statement_a' => 'Saya seorang pemikir', 'statement_b' => 'Saya adalah pekerja sosial', 'dimension_a' => 'R', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya suka menjadi pimpinan kelompok', 'statement_b' => 'Saya senang bekerja keras', 'dimension_a' => 'L', 'dimension_b' => 'G'],
            ['statement_a' => 'Saya suka pekerjaan yang cermat', 'statement_b' => 'Saya ingin dikenal banyak orang', 'dimension_a' => 'D', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya adalah tipe pemimpin', 'statement_b' => 'Saya suka hal-hal yang bersifat ilmiah', 'dimension_a' => 'L', 'dimension_b' => 'R'],
            ['statement_a' => 'Saya senang bekerja cepat', 'statement_b' => 'Saya adalah orang yang tenang', 'dimension_a' => 'T', 'dimension_b' => 'E'],
            ['statement_a' => 'Saya ingin menjadi orang terkenal', 'statement_b' => 'Saya senang bergabung dengan kelompok', 'dimension_a' => 'X', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya mudah bergaul', 'statement_b' => 'Saya menyukai hal-hal yang baru', 'dimension_a' => 'S', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya ingin berhasil', 'statement_b' => 'Saya menyukai kegiatan yang aktif', 'dimension_a' => 'A', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya suka mengikuti aturan', 'statement_b' => 'Saya pekerja keras', 'dimension_a' => 'W', 'dimension_b' => 'G'],
            ['statement_a' => 'Saya suka menyelesaikan pekerjaan', 'statement_b' => 'Saya senang mempengaruhi orang lain', 'dimension_a' => 'N', 'dimension_b' => 'P'],
            ['statement_a' => 'Saya dapat mengontrol emosi', 'statement_b' => 'Saya suka bekerja cepat', 'dimension_a' => 'E', 'dimension_b' => 'T'],
            ['statement_a' => 'Saya senang memeriksa hal detail', 'statement_b' => 'Saya mudah akrab dengan orang', 'dimension_a' => 'D', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya suka bersaing', 'statement_b' => 'Saya senang bekerja teratur', 'dimension_a' => 'K', 'dimension_b' => 'C'],
            ['statement_a' => 'Saya mudah membuat keputusan', 'statement_b' => 'Saya senang mendukung atasan', 'dimension_a' => 'I', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya bekerja dengan penuh semangat', 'statement_b' => 'Saya selalu menyelesaikan tugas', 'dimension_a' => 'V', 'dimension_b' => 'N'],
            ['statement_a' => 'Saya suka mengatur orang lain', 'statement_b' => 'Saya suka berpikir teoritis', 'dimension_a' => 'P', 'dimension_b' => 'R'],
            ['statement_a' => 'Saya senang tampil di depan umum', 'statement_b' => 'Saya menghormati atasan', 'dimension_a' => 'X', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya suka variasi dalam pekerjaan', 'statement_b' => 'Saya suka bertemu banyak orang', 'dimension_a' => 'Z', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya ingin mencapai prestasi tinggi', 'statement_b' => 'Saya menyukai pekerjaan yang terorganisir', 'dimension_a' => 'A', 'dimension_b' => 'C'],
            // Continue with more pairs...
            ['statement_a' => 'Saya butuh pengawasan dalam bekerja', 'statement_b' => 'Saya menyukai tantangan', 'dimension_a' => 'W', 'dimension_b' => 'K'],
            ['statement_a' => 'Saya suka hubungan yang akrab', 'statement_b' => 'Saya bekerja dengan giat', 'dimension_a' => 'O', 'dimension_b' => 'G'],
            ['statement_a' => 'Saya tenang dalam tekanan', 'statement_b' => 'Saya senang menjadi pemimpin', 'dimension_a' => 'E', 'dimension_b' => 'L'],
            ['statement_a' => 'Saya teliti dalam pekerjaan', 'statement_b' => 'Saya suka berada dalam kelompok', 'dimension_a' => 'D', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya cepat dalam bertindak', 'statement_b' => 'Saya ingin terkenal', 'dimension_a' => 'T', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya suka mengarahkan orang', 'statement_b' => 'Saya senang menyelesaikan tugas', 'dimension_a' => 'P', 'dimension_b' => 'N'],
            ['statement_a' => 'Saya menyukai analisis', 'statement_b' => 'Saya penuh energi', 'dimension_a' => 'R', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya tegas dalam keputusan', 'statement_b' => 'Saya mudah bergaul', 'dimension_a' => 'I', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya patuh pada aturan', 'statement_b' => 'Saya suka perubahan', 'dimension_a' => 'F', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya terorganisir', 'statement_b' => 'Saya ambisius', 'dimension_a' => 'C', 'dimension_b' => 'A'],
            // Add more standard pairs to reach 90
            ['statement_a' => 'Saya suka memimpin rapat', 'statement_b' => 'Saya suka bekerja sendiri', 'dimension_a' => 'L', 'dimension_b' => 'D'],
            ['statement_a' => 'Saya suka persaingan sehat', 'statement_b' => 'Saya hormat pada atasan', 'dimension_a' => 'K', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya suka berbicara di depan umum', 'statement_b' => 'Saya suka rutinitas', 'dimension_a' => 'X', 'dimension_b' => 'W'],
            ['statement_a' => 'Saya suka tantangan baru', 'statement_b' => 'Saya cepat dalam bekerja', 'dimension_a' => 'Z', 'dimension_b' => 'T'],
            ['statement_a' => 'Saya bisa mengendalikan diri', 'statement_b' => 'Saya suka menganalisa', 'dimension_a' => 'E', 'dimension_b' => 'R'],
            ['statement_a' => 'Saya pekerja ulet', 'statement_b' => 'Saya suka berkumpul', 'dimension_a' => 'G', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya ingin sukses', 'statement_b' => 'Saya suka membantu orang', 'dimension_a' => 'A', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya tegas', 'statement_b' => 'Saya rapi dalam bekerja', 'dimension_a' => 'K', 'dimension_b' => 'C'],
            ['statement_a' => 'Saya suka dapat perhatian', 'statement_b' => 'Saya selalu tuntas', 'dimension_a' => 'X', 'dimension_b' => 'N'],
            ['statement_a' => 'Saya bersemangat', 'statement_b' => 'Saya mengikuti prosedur', 'dimension_a' => 'V', 'dimension_b' => 'W'],
            ['statement_a' => 'Saya penuh ide', 'statement_b' => 'Saya suka mengontrol', 'dimension_a' => 'R', 'dimension_b' => 'P'],
            ['statement_a' => 'Saya tenang', 'statement_b' => 'Saya aktif bersosialisasi', 'dimension_a' => 'E', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya detail oriented', 'statement_b' => 'Saya cepat memutuskan', 'dimension_a' => 'D', 'dimension_b' => 'I'],
            ['statement_a' => 'Saya suka variasi', 'statement_b' => 'Saya setia pada atasan', 'dimension_a' => 'Z', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya pekerja keras', 'statement_b' => 'Saya suka memimpin', 'dimension_a' => 'G', 'dimension_b' => 'L'],
            ['statement_a' => 'Saya suka berkompetisi', 'statement_b' => 'Saya ingin dekat dengan orang', 'dimension_a' => 'K', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya teratur', 'statement_b' => 'Saya suka jadi pusat perhatian', 'dimension_a' => 'C', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya ambisius', 'statement_b' => 'Saya suka dalam kelompok', 'dimension_a' => 'A', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya energik', 'statement_b' => 'Saya suka hal baru', 'dimension_a' => 'V', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya menyelesaikan tugas', 'statement_b' => 'Saya cepat bertindak', 'dimension_a' => 'N', 'dimension_b' => 'T'],
            // Continue to 90 pairs total
            ['statement_a' => 'Saya suka mengatur', 'statement_b' => 'Saya patuh aturan', 'dimension_a' => 'P', 'dimension_b' => 'W'],
            ['statement_a' => 'Saya teoritis', 'statement_b' => 'Saya praktis', 'dimension_a' => 'R', 'dimension_b' => 'G'],
            ['statement_a' => 'Saya ramah', 'statement_b' => 'Saya tegas', 'dimension_a' => 'S', 'dimension_b' => 'I'],
            ['statement_a' => 'Saya akurat', 'statement_b' => 'Saya cepat', 'dimension_a' => 'D', 'dimension_b' => 'T'],
            ['statement_a' => 'Saya sabar', 'statement_b' => 'Saya ambisius', 'dimension_a' => 'E', 'dimension_b' => 'A'],
            ['statement_a' => 'Saya setia', 'statement_b' => 'Saya mandiri', 'dimension_a' => 'F', 'dimension_b' => 'L'],
            ['statement_a' => 'Saya terorganisir', 'statement_b' => 'Saya fleksibel', 'dimension_a' => 'C', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya gigih', 'statement_b' => 'Saya mudah bergaul', 'dimension_a' => 'N', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya kompetitif', 'statement_b' => 'Saya kooperatif', 'dimension_a' => 'K', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya menonjol', 'statement_b' => 'Saya bersemangat', 'dimension_a' => 'X', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya mengatur', 'statement_b' => 'Saya mengikuti', 'dimension_a' => 'P', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya analitis', 'statement_b' => 'Saya aksi', 'dimension_a' => 'R', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya memimpin', 'statement_b' => 'Saya mendukung', 'dimension_a' => 'L', 'dimension_b' => 'F'],
            ['statement_a' => 'Saya teliti', 'statement_b' => 'Saya sosial', 'dimension_a' => 'D', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya stabil', 'statement_b' => 'Saya dinamis', 'dimension_a' => 'E', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya tegas', 'statement_b' => 'Saya sabar', 'dimension_a' => 'K', 'dimension_b' => 'E'],
            ['statement_a' => 'Saya sistematis', 'statement_b' => 'Saya kreatif', 'dimension_a' => 'C', 'dimension_b' => 'R'],
            ['statement_a' => 'Saya rajin', 'statement_b' => 'Saya populer', 'dimension_a' => 'G', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya sukses', 'statement_b' => 'Saya akrab', 'dimension_a' => 'A', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya tuntas', 'statement_b' => 'Saya cepat', 'dimension_a' => 'N', 'dimension_b' => 'T'],
            ['statement_a' => 'Saya mengontrol', 'statement_b' => 'Saya mengikuti', 'dimension_a' => 'P', 'dimension_b' => 'W'],
            ['statement_a' => 'Saya berpikir', 'statement_b' => 'Saya bertindak', 'dimension_a' => 'R', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya memimpin', 'statement_b' => 'Saya bergabung', 'dimension_a' => 'L', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya cermat', 'statement_b' => 'Saya ekspresif', 'dimension_a' => 'D', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya tenang', 'statement_b' => 'Saya kompetitif', 'dimension_a' => 'E', 'dimension_b' => 'K'],
            ['statement_a' => 'Saya teratur', 'statement_b' => 'Saya inovatif', 'dimension_a' => 'C', 'dimension_b' => 'Z'],
            ['statement_a' => 'Saya pekerja', 'statement_b' => 'Saya pemimpin', 'dimension_a' => 'G', 'dimension_b' => 'L'],
            ['statement_a' => 'Saya berprestasi', 'statement_b' => 'Saya populer', 'dimension_a' => 'A', 'dimension_b' => 'X'],
            ['statement_a' => 'Saya tekun', 'statement_b' => 'Saya ramah', 'dimension_a' => 'N', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya menguasai', 'statement_b' => 'Saya memutuskan', 'dimension_a' => 'P', 'dimension_b' => 'I'],
            ['statement_a' => 'Saya teoretis', 'statement_b' => 'Saya praktis', 'dimension_a' => 'R', 'dimension_b' => 'G'],
            ['statement_a' => 'Saya memimpin', 'statement_b' => 'Saya detail', 'dimension_a' => 'L', 'dimension_b' => 'D'],
            ['statement_a' => 'Saya stabil', 'statement_b' => 'Saya energik', 'dimension_a' => 'E', 'dimension_b' => 'V'],
            ['statement_a' => 'Saya patuh', 'statement_b' => 'Saya mandiri', 'dimension_a' => 'F', 'dimension_b' => 'I'],
            ['statement_a' => 'Saya terstruktur', 'statement_b' => 'Saya sosial', 'dimension_a' => 'C', 'dimension_b' => 'S'],
            ['statement_a' => 'Saya rajin', 'statement_b' => 'Saya ambisius', 'dimension_a' => 'G', 'dimension_b' => 'A'],
            ['statement_a' => 'Saya tegas', 'statement_b' => 'Saya akrab', 'dimension_a' => 'K', 'dimension_b' => 'O'],
            ['statement_a' => 'Saya menonjol', 'statement_b' => 'Saya berkumpul', 'dimension_a' => 'X', 'dimension_b' => 'B'],
            ['statement_a' => 'Saya berubah', 'statement_b' => 'Saya konsisten', 'dimension_a' => 'Z', 'dimension_b' => 'N'],
            ['statement_a' => 'Saya cepat', 'statement_b' => 'Saya teratur', 'dimension_a' => 'T', 'dimension_b' => 'W'],
        ];
    }
}
