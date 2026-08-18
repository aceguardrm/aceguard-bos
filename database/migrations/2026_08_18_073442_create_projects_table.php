<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Organisation
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Project Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->string('owner_name')
                ->nullable();

            $table->string('owner_email')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Project Status
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('planned');

            $table->string('priority')
                ->default('medium');


            /*
            |--------------------------------------------------------------------------
            | Delivery
            |--------------------------------------------------------------------------
            */

            $table->date('start_date')
                ->nullable();

            $table->date('due_date')
                ->nullable();

            $table->unsignedTinyInteger('progress')
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Source / Intelligence Link
            |--------------------------------------------------------------------------
            */

            $table->string('source')
                ->nullable();

            $table->string('source_reference')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            $table->text('notes')
                ->nullable();


            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('status');
            $table->index('priority');
            $table->index('due_date');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};