<?php

namespace App\Console\Commands;

use App\Models\JocCard;
use App\Models\PbgRequest;
use App\Models\PermintaanKendaraan;
use App\Models\PortalItem;
use App\Models\RptkRequest;
use App\Models\SasaranMutu;
use App\Services\ArsipDivisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

/**
 * Mengarsipkan pengajuan yang belum punya salinan PDF di folder divisi.
 *
 * Dipakai untuk menyusulkan data lama yang dibuat sebelum fitur pengarsipan
 * ada, dan aman dijalankan berulang: yang sudah punya arsip dilewati.
 */
class ArsipkanPengajuan extends Command
{
    protected $signature = 'portal:arsipkan {--paksa : Perbarui juga yang sudah punya arsip}';

    protected $description = 'Arsipkan JOC, RPTK, Sasaran Mutu, PBG, dan permintaan kendaraan ke folder divisi';

    public function handle(ArsipDivisi $arsip): int
    {
        $jumlah = 0;

        foreach (JocCard::with('project')->get() as $kartu) {
            $nama = str_replace('/', '-', $kartu->nomor) . ' ' . $kartu->lokasi . '.pdf';
            if (! $this->option('paksa') && $this->sudahAda($nama)) {
                continue;
            }

            $arsip->simpanPdf('joc', $nama, Pdf::loadView('portal.joc.pdf', ['kartu' => $kartu])->setPaper('a4')->output());
            $this->line('  JOC  ' . $kartu->nomor . ' -> ' . $nama);
            $jumlah++;
        }

        foreach (RptkRequest::all() as $rptk) {
            $nama = str_replace('/', '-', $rptk->nomor) . ' ' . $rptk->jabatan . '.pdf';
            if (! $this->option('paksa') && $this->sudahAda($nama)) {
                continue;
            }

            $arsip->simpanPdf('rptk', $nama, Pdf::loadView('portal.rptk.pdf', ['rptk' => $rptk])->setPaper('a4')->output());
            $this->line('  RPTK ' . $rptk->nomor . ' -> ' . $nama);
            $jumlah++;
        }

        foreach (SasaranMutu::all() as $laporan) {
            $nama = str_replace('/', '-', $laporan->nomor) . ' ' . $laporan->departemen
                . ' ' . $laporan->labelBulan() . '.pdf';
            if (! $this->option('paksa') && $this->sudahAda($nama)) {
                continue;
            }

            $arsip->simpanPdf('sasaran_mutu', $nama,
                Pdf::loadView('portal.sasaran-mutu.pdf', ['laporan' => $laporan])->setPaper('a4', 'landscape')->output());
            $this->line('  SM   ' . $laporan->nomor . ' -> ' . $nama);
            $jumlah++;
        }

        foreach (PermintaanKendaraan::all() as $p) {
            $nama = str_replace('/', '-', $p->nomor) . ' ' . $p->nama . '.pdf';
            if (! $this->option('paksa') && $this->sudahAda($nama)) {
                continue;
            }

            $arsip->simpanPdf('kendaraan', $nama,
                Pdf::loadView('portal.kendaraan.pdf', ['permintaan' => $p])->setPaper('a5')->output());
            $this->line('  PK   ' . $p->nomor . ' -> ' . $nama);
            $jumlah++;
        }

        foreach (PbgRequest::all() as $pbg) {
            $nama = str_replace('/', '-', $pbg->nomor) . ' ' . $pbg->departemen . '.pdf';
            if (! $this->option('paksa') && $this->sudahAda($nama)) {
                continue;
            }

            $arsip->simpanPdf('pbg', $nama,
                Pdf::loadView('portal.pbg.pdf', ['pbg' => $pbg])->setPaper('a4', 'landscape')->output());
            $this->line('  PBG  ' . $pbg->nomor . ' -> ' . $nama);
            $jumlah++;
        }

        $this->info($jumlah . ' dokumen diarsipkan.');

        return self::SUCCESS;
    }

    private function sudahAda(string $nama): bool
    {
        return PortalItem::where('name', $nama)->whereNull('trashed_at')->exists();
    }
}
