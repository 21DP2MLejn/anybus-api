<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Services\Job\JobFeedService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JobFeedController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobFeedService $jobFeedService
    ) {}

    #[OA\Get(
        path: '/jobs/feed',
        summary: 'Get job feed for authenticated worker',
        security: [['sanctum' => []]],
        tags: ['Job'],
        parameters: [
            new OA\Parameter(name: 'radius', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'min_price', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'max_price', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'sort_by', in: 'query', schema: new OA\Schema(type: 'string', enum: ['distance', 'price', 'created_at'], default: 'distance')),
            new OA\Parameter(name: 'sort_order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Job feed retrieved successfully'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $worker = $request->user()->worker;

        if (! $worker) {
            return $this->errorResponse('Worker profile not found.', 404);
        }

        $filters = [
            'radius' => $request->input('radius', 10),
            'category' => $request->input('category'),
            'skills' => $request->input('skills', []),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort_by' => $request->input('sort_by', 'distance'),
            'sort_order' => $request->input('sort_order', 'asc'),
            'per_page' => $request->input('per_page', 20),
        ];

        $feed = $this->jobFeedService->getFeedForWorker($worker, $filters);

        return $this->successResponse($feed, 'Job feed retrieved successfully.');
    }
}
