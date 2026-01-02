<?php

namespace App\Http\Controllers\Job;

use App\Actions\Job\CreateJobAction;
use App\DTO\Job\CreateJobDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Services\Job\JobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CreateJobAction $createJobAction,
        private readonly JobService $jobService
    ) {}

    /**
     * Create a new job.
     */
    public function store(CreateJobRequest $request): JsonResponse
    {
        $dto = CreateJobDTO::fromArray([
            'customer_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $job = $this->createJobAction->execute($dto);

        return $this->successResponse(
            new JobResource($job),
            'Job created successfully.',
            201
        );
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job): JsonResponse
    {
        return $this->successResponse(
            new JobResource($job),
            'Job retrieved successfully.'
        );
    }

    /**
     * List user's jobs (customer or worker).
     */
    public function index(Request $request): JsonResponse
    {
        $jobs = $this->jobService->getUserJobs($request->user());

        return $this->successResponse(
            JobResource::collection($jobs),
            'Jobs retrieved successfully.'
        );
    }
}
