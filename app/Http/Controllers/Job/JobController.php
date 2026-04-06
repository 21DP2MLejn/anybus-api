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
use OpenApi\Attributes as OA;

class JobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CreateJobAction $createJobAction,
        private readonly JobService $jobService
    ) {}

    #[OA\Post(
        path: '/jobs',
        summary: 'Create a new job',
        security: [['sanctum' => []]],
        tags: ['Job'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'category', 'price', 'latitude', 'longitude'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Fix my leaky faucet'),
                    new OA\Property(property: 'description', type: 'string', example: 'The faucet in my kitchen is leaking.'),
                    new OA\Property(property: 'category', type: 'string', example: 'Plumbing'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 50.00),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 59.4370),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 24.7536),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Job created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
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

    #[OA\Get(
        path: '/jobs/{job}',
        summary: 'Display the specified job',
        security: [['sanctum' => []]],
        tags: ['Job'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Job retrieved successfully'),
            new OA\Response(response: 404, description: 'Job not found'),
        ]
    )]
    public function show(Job $job): JsonResponse
    {
        return $this->successResponse(
            new JobResource($job),
            'Job retrieved successfully.'
        );
    }

    #[OA\Get(
        path: '/jobs',
        summary: "List user's jobs (customer or worker)",
        security: [['sanctum' => []]],
        tags: ['Job'],
        responses: [
            new OA\Response(response: 200, description: 'Jobs retrieved successfully'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $jobs = $this->jobService->getUserJobs($request->user());

        return $this->successResponse(
            JobResource::collection($jobs),
            'Jobs retrieved successfully.'
        );
    }
}
