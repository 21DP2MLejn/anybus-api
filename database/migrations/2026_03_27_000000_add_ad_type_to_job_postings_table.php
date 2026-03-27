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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->string('ad_type', 32)->default('customer')->after('customer_id');
            $table->index('ad_type');
        });

        // Best-effort backfill for historical worker ads:
        // In `storeWorkerAd` we set:
        // - customer_id = worker's user_id
        // - accepted_worker_id = that user's worker.id
        DB::statement("
            UPDATE job_postings jp
            SET ad_type = 'worker'
            FROM workers w
            WHERE w.user_id = jp.customer_id
              AND jp.accepted_worker_id = w.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropIndex(['ad_type']);
            $table->dropColumn('ad_type');
        });
    }
};

