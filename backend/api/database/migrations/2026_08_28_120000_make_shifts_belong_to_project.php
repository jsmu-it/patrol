<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shift dipisah per project.
 *
 * Sebelumnya tabel `shifts` bersifat global dengan `code` unik, dan project
 * hanya mencentang shift mana yang dipakai lewat pivot `project_shift`.
 * Akibatnya shift milik satu project ikut muncul di daftar project lain, dan
 * dua project tidak bisa punya "Pagi" dengan jam berbeda.
 *
 * Migrasi ini memberi setiap shift satu pemilik (`project_id`). Baris pivot
 * yang ada dipakai sebagai sumber kebenaran: bila sebuah shift dipakai oleh
 * beberapa project, shift itu digandakan supaya masing-masing project punya
 * salinannya sendiri.
 *
 * Tabel `project_shift` sengaja TIDAK dihapus — isinya dibiarkan utuh sebagai
 * jejak agar migrasi ini bisa dibalik tanpa kehilangan data.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Riwayat absensi tidak boleh ikut terhapus saat sebuah shift dihapus.
        //    Sebelumnya FK ini ON DELETE CASCADE: menghapus satu shift akan
        //    menghapus seluruh absensi yang merujuknya tanpa peringatan.
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign('attendance_logs_shift_id_foreign');
        });
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->restrictOnDelete();
        });

        // 2. Pemilik shift.
        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')
                  ->constrained()->cascadeOnDelete();
        });

        // 3. Pindahkan kepemilikan berdasarkan pivot yang ada.
        $pivots = DB::table('project_shift')->orderBy('shift_id')->orderBy('project_id')->get();
        $sudahDipakai = [];   // shift_id => project_id pertama yang memakainya

        foreach ($pivots as $pivot) {
            if (! isset($sudahDipakai[$pivot->shift_id])) {
                // Project pertama mewarisi baris shift aslinya.
                DB::table('shifts')->where('id', $pivot->shift_id)
                    ->update(['project_id' => $pivot->project_id]);
                $sudahDipakai[$pivot->shift_id] = $pivot->project_id;
                continue;
            }

            // Project berikutnya mendapat salinan tersendiri.
            $asli = DB::table('shifts')->where('id', $pivot->shift_id)->first();
            if (! $asli) {
                continue;
            }

            $salinanId = DB::table('shifts')->insertGetId([
                'project_id'        => $pivot->project_id,
                'name'              => $asli->name,
                'code'              => $asli->code,
                'start_time'        => $asli->start_time,
                'end_time'          => $asli->end_time,
                'tolerance_minutes' => $asli->tolerance_minutes,
                'is_default'        => $asli->is_default,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Absensi milik project ini diarahkan ke salinannya.
            DB::table('attendance_logs')
                ->where('project_id', $pivot->project_id)
                ->where('shift_id', $pivot->shift_id)
                ->update(['shift_id' => $salinanId]);
        }

        // 4. `code` cukup unik di dalam satu project, karena kini boleh berulang
        //    antar project (setiap project punya "SHIFT_PAGI"-nya sendiri).
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique('shifts_code_unique');
            $table->unique(['project_id', 'code'], 'shifts_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique('shifts_project_code_unique');
        });

        // Buang shift hasil penggandaan: sisakan satu baris per kode.
        $duplikat = DB::table('shifts')
            ->select('code', DB::raw('MIN(id) as id_pertama'))
            ->groupBy('code')->get();
        foreach ($duplikat as $row) {
            DB::table('attendance_logs')
                ->whereIn('shift_id', function ($q) use ($row) {
                    $q->select('id')->from('shifts')
                      ->where('code', $row->code)->where('id', '!=', $row->id_pertama);
                })
                ->update(['shift_id' => $row->id_pertama]);
            DB::table('shifts')->where('code', $row->code)
                ->where('id', '!=', $row->id_pertama)->delete();
        }

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->unique('code', 'shifts_code_unique');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign('attendance_logs_shift_id_foreign');
        });
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->cascadeOnDelete();
        });
    }
};
