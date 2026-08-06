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
        Schema::create('security_controls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category');
            $table->string('control');

            $table->boolean('enabled')
                ->default(false);

            $table->unsignedInteger('points')
                ->default(0);

            $table->unsignedInteger('maximum_points')
                ->default(10);

            $table->text('notes')
                ->nullable();

            $table->string('evidence')
                ->nullable();

            $table->timestamp('last_reviewed_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'client_id',
                'category',
                'control',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_controls');
    }
};