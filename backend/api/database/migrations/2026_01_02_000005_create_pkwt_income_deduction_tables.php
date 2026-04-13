<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PKWT Income Items (many-to-many with amounts)
        Schema::create('pkwt_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkwt_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pkwt_income_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['pkwt_record_id', 'pkwt_income_type_id']);
        });

        // PKWT Deduction Items (many-to-many with amounts)
        Schema::create('pkwt_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkwt_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pkwt_deduction_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['pkwt_record_id', 'pkwt_deduction_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkwt_deductions');
        Schema::dropIfExists('pkwt_incomes');
    }
};
