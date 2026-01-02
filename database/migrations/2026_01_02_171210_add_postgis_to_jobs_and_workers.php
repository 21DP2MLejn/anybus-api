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
        // Enable PostGIS extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        // Add geography column to job_postings table
        Schema::table('job_postings', function (Blueprint $table) {
            $table->geography('location')->nullable()->after('longitude');
        });

        // Add geography column to workers table
        Schema::table('workers', function (Blueprint $table) {
            $table->geography('location')->nullable()->after('longitude');
        });

        // Migrate existing lat/lng to geography columns
        DB::statement('
            UPDATE job_postings
            SET location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL
        ');

        DB::statement('
            UPDATE workers
            SET location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL
        ');

        // Create spatial indexes
        DB::statement('CREATE INDEX IF NOT EXISTS job_postings_location_idx ON job_postings USING GIST (location);');
        DB::statement('CREATE INDEX IF NOT EXISTS workers_location_idx ON workers USING GIST (location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
