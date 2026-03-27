<?php

namespace App\Http\Controllers\Job;

use App\Actions\Job\CreateJobAction;
use App\DTO\Job\CreateJobDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateJobRequest;
use App\Http\Requests\CreateWorkerJobRequest;
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
     * Create a new worker advertisement.
     */
    public function storeWorkerAd(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isWorker() || !$user->worker) {
            return $this->errorResponse('Only workers can create worker advertisements.', 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'contact' => ['required', 'string', 'max:255'],
        ]);

        $job = \App\Models\Job::create([
            'customer_id' => $user->id, 
            'accepted_worker_id' => $user->worker->id, 
            'ad_type' => 'worker',
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => 'open', 
        ]);
        
        \App\Models\JobEvent::create([
            'job_id' => $job->id,
            'actor_user_id' => $user->id,
            'action' => \App\Enums\JobAction::ACCEPT_JOB->value,
            'from_state' => \App\Enums\JobStatus::OPEN->value,
            'to_state' => \App\Enums\JobStatus::OPEN->value,
        ]);

        return $this->successResponse(
            new JobResource($job),
            'Worker advertisement created successfully.',
            201
        );
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job, Request $request): JsonResponse
    {
        $user = $request->user();

        // Enforce role-based visibility for "browsing" (defense-in-depth).
        // Always allow:
        // - owner (customer_id)
        // - accepted worker
        // - admin
        $isOwner = $job->customer_id === $user->id;
        $isAcceptedWorker = $user->isWorker() && $user->worker && $job->accepted_worker_id === $user->worker->id;
        $isAdmin = $user->hasRole('admin');

        $canBrowseByRole =
            ($user->hasRole(\App\Models\User::ROLE_CUSTOMER) && $job->ad_type === 'worker') ||
            ($user->isWorker() && $job->ad_type === 'customer');

        if (! ($isOwner || $isAcceptedWorker || $isAdmin || $canBrowseByRole)) {
            return $this->errorResponse('You are not authorized to view this advertisement.', 403);
        }

        $job->load('customer', 'acceptedWorker.user');
        return $this->successResponse(
            new JobResource($job),
            'Job retrieved successfully.'
        );
    }

    /**
     * List all public jobs/advertisements.
     */
    public function allJobs(Request $request): JsonResponse
    {
        $jobs = $this->jobService->getAllJobs($request->user());

        return $this->successResponse(
            JobResource::collection($jobs),
            'All jobs retrieved successfully.'
        );
    }

    /**
     * List user's jobs (customer or worker).
     */
    public function index(Request $request): JsonResponse
    {
        // Check if user wants all jobs (for public view)
        if ($request->get('all') === 'true') {
            $jobs = $this->jobService->getAllJobs($request->user());
        } else {
            $jobs = $this->jobService->getUserJobs($request->user());
        }

        return $this->successResponse(
            JobResource::collection($jobs),
            'Jobs retrieved successfully.'
        );
    }
}
