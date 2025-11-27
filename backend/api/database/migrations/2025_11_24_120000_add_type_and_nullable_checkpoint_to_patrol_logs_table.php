<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patrol_logs', function (Blueprint $table): void {
            $table->string('type')->default('patrol')->after('checkpoint_id');

            $table->dropForeign(['checkpoint_id']);
        });

        Schema::table('patrol_logs', function (Blueprint $table): void {
            $table->unsignedBigInteger('checkpoint_id')->nullable()->change();
            $table->foreign('checkpoint_id')
                ->references('id')
                ->on('checkpoints')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patrol_logs', function (Blueprint $table): void {
            $table->dropForeign(['checkpoint_id']);
            $table->unsignedBigInteger('checkpoint_id')->nullable(false)->change();
            $table->foreign('checkpoint_id')
                ->references('id')
                ->on('checkpoints')
                ->cascadeOnDelete();

            $table->dropColumn('type');
        });
    }
};
