<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_pulse_assessments', function (Blueprint $table) {
            $table->id();

            // Workspace / client being assessed
            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            // Core Business Pulse™ assessment domains
            $table->unsignedTinyInteger('operations_score')->default(0);
            $table->unsignedTinyInteger('continuity_score')->default(0);
            $table->unsignedTinyInteger('documentation_score')->default(0);
            $table->unsignedTinyInteger('compliance_score')->default(0);
            $table->unsignedTinyInteger('technology_score')->default(0);
            $table->unsignedTinyInteger('readiness_score')->default(0);

            // Calculated overall Business Health score
            $table->unsignedTinyInteger('overall_score')->default(0);

            // Assessment status
            $table->string('status')->default('draft');

            // Management observations
            $table->text('notes')->nullable();

            // Assessment tracking
            $table->timestamp('assessed_at')->nullable();

            $table->timestamps();

            // One current Business Pulse™ assessment per workspace
            $table->unique('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_pulse_assessments');
    }
};