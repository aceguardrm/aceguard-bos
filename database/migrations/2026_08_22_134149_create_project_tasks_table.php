<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Parent Project
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Task Identity
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->text('description')
                ->nullable();

            $table->boolean('is_milestone')
                ->default(false);


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
            | Delivery State
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('pending');

            $table->string('priority')
                ->default('medium');

            $table->date('due_date')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);


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

            $table->index([
                'project_id',
                'status',
            ]);

            $table->index('priority');

            $table->index('due_date');

            $table->index('sort_order');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};