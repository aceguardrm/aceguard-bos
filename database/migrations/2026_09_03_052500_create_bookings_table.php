<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('service');
            $table->unsignedSmallInteger('duration_minutes');
            $table->dateTime('appointment_at');
            $table->dateTime('ends_at');
            $table->string('timezone')->default('Europe/London');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('confirmed');
            $table->timestamps();

            $table->index(['appointment_at', 'ends_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
