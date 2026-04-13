<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkwt_records', function (Blueprint $table) {
            $table->id();
            $table->string('pkwt_number', 20)->unique();
            
            // Source reference
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            
            // Personal data from applicant
            $table->string('ktp_number', 20)->nullable();
            $table->string('name');
            $table->string('gender', 20)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            
            // Work assignment
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            
            // Contract period
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            
            // Leave type reference
            $table->foreignId('leave_type_id')->nullable()->constrained()->nullOnDelete();
            
            // Status: draft, active, sent, signed, expired, terminated
            $table->string('status', 20)->default('draft');
            
            // Linked user (created when status becomes active)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Timestamps for workflow
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkwt_records');
    }
};
