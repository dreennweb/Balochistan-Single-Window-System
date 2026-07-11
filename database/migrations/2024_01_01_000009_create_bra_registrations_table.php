<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bra_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_application_id')->constrained('department_applications')->onDelete('cascade');
            $table->enum('registration_type', ['withholding_agent', 'service_provider'])->default('service_provider');
            $table->string('sales_tax_number')->nullable();
            $table->boolean('wwf_applicable')->default(false);
            $table->string('wwf_registration_number')->nullable();
            $table->timestamps();

            $table->unique('department_application_id');
            $table->index('registration_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bra_registrations');
    }
};
