<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWorkerLocationRequest;
use App\Services\Geolocation\GeolocationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class WorkerLocationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GeolocationService $geolocationService
    ) {}

    #[OA\Post(
        path: '/worker/location',
        summary: 'Update worker current location',
        description: "Updates the worker's current latitude and longitude. Optionally associates with a job for tracking.",
        security: [['sanctum' => []]],
        tags: ['Worker'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['latitude', 'longitude'],
                properties: [
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 59.4370),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 24.7536),
                    new OA\Property(property: 'job_id', type: 'integer', example: 1, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Location updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'latitude', type: 'number', format: 'float'),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden - Not a worker'),
            new OA\Response(response: 429, description: 'Too Many Requests'),
        ]
    )]
    public function update(UpdateWorkerLocationRequest $request): JsonResponse
    {
        $user = $request->user();
        $worker = $user->worker;

        if (! $user->isWorker() || ! $worker) {
            return $this->errorResponse('Worker profile not found or user is not a driver.', 403);
        }

        $validated = $request->validated();

        $this->geolocationService->updateWorkerLocation(
            worker: $worker,
            latitude: $validated['latitude'],
            longitude: $validated['longitude'],
            jobId: $validated['job_id'] ?? null
        );

        return $this->successResponse([
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'updated_at' => now()->toIso8601String(),
        ], 'Location updated successfully.');
    }
}
