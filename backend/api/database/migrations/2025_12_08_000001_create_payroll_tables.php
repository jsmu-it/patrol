<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('period_month', 20); // e.g. "November 2025"
            $table->string('nip')->nullable();
            $table->string('name');
            $table->string('unit')->nullable(); // Unit / Site
            $table->string('position')->nullable(); // Jabatan
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('net_income', 15, 2)->default(0);
            $table->string('sign_location')->nullable(); // e.g. "Jakarta"
            $table->date('sign_date')->nullable();
            $table->timestamps();

            $table->index(['period_month', 'user_id']);
        });

        Schema::create('payroll_slip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_slip_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['income', 'deduction']);
            $table->string('label'); // e.g. "Gaji", "BPJS TK - JHT"
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['payroll_slip_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_items');
        Schema::dropIfExists('payroll_slips');
    }
};
