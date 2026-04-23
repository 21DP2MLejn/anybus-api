<?php

namespace App\Http\Controllers\Job;

use App\Commands\JobTransitionCommand;
use App\Enums\JobAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobTransitionRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JobActionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobTransitionCommand $jobTransitionCommand
    ) {}

    #[OA\Post(
        path: '/jobs/{job}/accept',
        summary: 'Accept a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Job accepted successfully'),
            new OA\Response(response: 400, description: 'Bad request'),
        ]
    )]
    public function accept(Job $job, Request $request): JsonResponse
    {
        try {
            $worker = $request->user()->worker;

            if (! $worker) {
                return $this->errorResponse('Worker profile not found.', 404);
            }

            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::ACCEPT_JOB
            );

            return $this->successResponse(
                new JobResource($job),
                'Job accepted successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    #[OA\Post(
        path: '/jobs/{job}/investigate',
        summary: 'Start investigating a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Arrived at location'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job investigation started successfully'),
        ]
    )]
    public function investigate(Job $job, JobTransitionRequest $request): JsonResponse
    {
        try {
            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::START_INVESTIGATION,
                $request->input('comment'),
                $request->input('metadata', [])
            );

            return $this->successResponse(
                new JobResource($job),
                'Job investigation started successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    #[OA\Post(
        path: '/jobs/{job}/prepare',
        summary: 'Start preparing for a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Getting tools ready'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job preparation started successfully'),
        ]
    )]
    public function prepare(Job $job, JobTransitionRequest $request): JsonResponse
    {
        try {
            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::START_PREPARATION,
                $request->input('comment'),
                $request->input('metadata', [])
            );

            return $this->successResponse(
                new JobResource($job),
                'Job preparation started successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    #[OA\Post(
        path: '/jobs/{job}/start',
        summary: 'Start a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Starting the work'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job started successfully'),
        ]
    )]
    public function start(Job $job, JobTransitionRequest $request): JsonResponse
    {
        try {
            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::START_JOB,
                $request->input('comment'),
                $request->input('metadata', [])
            );

            return $this->successResponse(
                new JobResource($job),
                'Job started successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    #[OA\Post(
        path: '/jobs/{job}/complete',
        summary: 'Complete a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Work finished'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job completed successfully'),
        ]
    )]
    public function complete(Job $job, JobTransitionRequest $request): JsonResponse
    {
        try {
            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::COMPLETE_JOB,
                $request->input('comment'),
                $request->input('metadata', [])
            );

            return $this->successResponse(
                new JobResource($job),
                'Job completed successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    #[OA\Post(
        path: '/jobs/{job}/cancel',
        summary: 'Cancel a job',
        security: [['sanctum' => []]],
        tags: ['Job Actions'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Cannot complete work'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job cancelled successfully'),
        ]
    )]
    public function cancel(Job $job, JobTransitionRequest $request): JsonResponse
    {
        try {
            $job = $this->jobTransitionCommand->execute(
                $job,
                $request->user(),
                JobAction::CANCEL_JOB,
                $request->input('comment'),
                $request->input('metadata', [])
            );

            return $this->successResponse(
                new JobResource($job),
                'Job cancelled successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
