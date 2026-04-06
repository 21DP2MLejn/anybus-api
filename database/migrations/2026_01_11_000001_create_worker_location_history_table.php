<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worker_location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('job_postings')->onDelete('set null');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->geography('location');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('worker_id');
            $table->index('job_id');
            $table->index('recorded_at');
        });

        // Create spatial index
        DB::statement('CREATE INDEX IF NOT EXISTS worker_location_history_location_idx ON worker_location_history USING GIST (location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_location_history');
    }
};
