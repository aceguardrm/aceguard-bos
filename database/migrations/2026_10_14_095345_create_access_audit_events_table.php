<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_audit_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedBigInteger('subject_user_id')->nullable();
            $table->unsignedBigInteger('invitation_id')->nullable();
            $table->string('source', 20);
            $table->string('action', 80);
            $table->string('previous_role', 20)->nullable();
            $table->string('new_role', 20)->nullable();
            $table->timestamp('created_at');

            $table->index(['client_id', 'created_at']);
            $table->index(['subject_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_audit_events');
    }
};
