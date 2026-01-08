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
        Schema::create('job_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->onDelete('cascade');
            $table->foreignId('actor_user_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // accept_job, start_investigation, etc.
            $table->string('from_state');
            $table->string('to_state');
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['job_id', 'created_at']);
            $table->index(['actor_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_events');
    }
};
