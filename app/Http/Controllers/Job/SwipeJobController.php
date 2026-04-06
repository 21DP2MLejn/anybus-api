<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwipeJobRequest;
use App\Models\Job;
use App\Services\Job\SwipeJobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SwipeJobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SwipeJobService $swipeJobService
    ) {}

    #[OA\Post(
        path: '/jobs/{job}/swipe',
        summary: 'Swipe on a job (like or skip)',
        security: [['sanctum' => []]],
        tags: ['Job'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['action'],
                properties: [
                    new OA\Property(property: 'action', type: 'string', enum: ['like', 'skip'], example: 'like'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job interaction recorded successfully'),
        ]
    )]
    public function swipe(Job $job, SwipeJobRequest $request): JsonResponse
    {
        $worker = $request->user()->worker;

        if (! $worker) {
            return $this->errorResponse('Worker profile not found.', 404);
        }

        $interaction = $this->swipeJobService->swipe(
            $job,
            $worker,
            $request->validated()['action']
        );

        return $this->successResponse(
            [
                'interaction' => $interaction,
                'job' => new \App\Http\Resources\JobResource($job),
            ],
            'Job interaction recorded successfully.'
        );
    }
}
