<?php

namespace App\Actions\Job;

use App\DTO\Job\CreateJobDTO;
use App\Enums\JobStatus;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class CreateJobAction
{
    /**
     * Create a new job.
     */
    public function execute(CreateJobDTO $dto): Job
    {
        return DB::transaction(function () use ($dto) {
            $job = Job::create([
                'customer_id' => $dto->customer_id,
                'title' => $dto->title,
                'description' => $dto->description,
                'category' => $dto->category,
                'price' => $dto->price,
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
                'status' => JobStatus::OPEN,
            ]);

            // Create PostGIS location geography column
            if ($job->latitude && $job->longitude) {
                DB::statement(
                    'UPDATE job_postings SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
                    [$job->longitude, $job->latitude, $job->id]
                );
                $job->refresh();
            }

            return $job;
        });
    }
}
