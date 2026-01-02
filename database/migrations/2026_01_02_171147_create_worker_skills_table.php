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
        Schema::create('worker_skills', function (Blueprint $table) {
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            $table->string('skill');
            $table->primary(['worker_id', 'skill']);

            $table->index('worker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_skills');
    }
};
