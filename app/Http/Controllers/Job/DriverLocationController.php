<?php

namespace App\Http\Controllers\Job;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\Geolocation\GeolocationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DriverLocationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GeolocationService $geolocationService
    ) {}

    #[OA\Get(
        path: '/jobs/{job}/driver-location',
        summary: 'Get current driver location for a job',
        description: 'Returns the current location of the driver assigned to an in-progress job.',
        security: [['sanctum' => []]],
        tags: ['Job'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Driver location retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float'),
                            new OA\Property(property: 'distance_km', type: 'number', format: 'float'),
                            new OA\Property(property: 'eta_minutes', type: 'integer'),
                            new OA\Property(property: 'last_updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Unauthorized - Not the customer'),
            new OA\Response(response: 404, description: 'Job or location not found'),
        ]
    )]
    public function show(Request $request, Job $job): JsonResponse
    {
        $user = $request->user();

        // Check if user is the customer for this job
        if ($job->customer_id !== $user->id) {
            return $this->errorResponse('You are not authorized to view this job\'s driver location.', 403);
        }

        // Check if job is in progress
        if ($job->status !== JobStatus::IN_PROGRESS) {
            return $this->errorResponse('Driver location is only available for jobs in progress.', 400);
        }

        // Check if job has an accepted worker
        if (! $job->accepted_worker_id) {
            return $this->errorResponse('No driver assigned to this job.', 404);
        }

        $locationData = $this->geolocationService->getDriverLocationForJob($job);

        if (! $locationData) {
            return $this->errorResponse('Driver location not available.', 404);
        }

        return $this->successResponse($locationData, 'Driver location retrieved successfully.');
    }

    #[OA\Get(
        path: '/jobs/{job}/route',
        summary: 'Get driver route history for a job',
        description: 'Returns the full route history of the driver for an in-progress or completed job.',
        security: [['sanctum' => []]],
        tags: ['Job'],
        parameters: [
            new OA\Parameter(name: 'job', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Route history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'route', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                                    new OA\Property(property: 'longitude', type: 'number', format: 'float'),
                                    new OA\Property(property: 'recorded_at', type: 'string', format: 'date-time'),
                                ]
                            )),
                            new OA\Property(property: 'total_points', type: 'integer'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Unauthorized - Not the customer'),
        ]
    )]
    public function route(Request $request, Job $job): JsonResponse
    {
        $user = $request->user();

        // Check if user is the customer for this job
        if ($job->customer_id !== $user->id) {
            return $this->errorResponse('You are not authorized to view this job\'s route.', 403);
        }

        // Check if job is in progress or completed
        if (! in_array($job->status, [JobStatus::IN_PROGRESS, JobStatus::COMPLETED])) {
            return $this->errorResponse('Route is only available for jobs in progress or completed.', 400);
        }

        $routeHistory = $this->geolocationService->getRouteHistory($job->id);

        return $this->successResponse([
            'route' => $routeHistory->map(fn ($point) => [
                'latitude' => (float) $point->latitude,
                'longitude' => (float) $point->longitude,
                'recorded_at' => $point->recorded_at->toIso8601String(),
            ]),
            'total_points' => $routeHistory->count(),
        ], 'Route history retrieved successfully.');
    }
}
