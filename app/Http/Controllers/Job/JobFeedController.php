<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\Job\JobFeedService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobFeedController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobFeedService $jobFeedService
    ) {}

    /**
     * Get job feed for authenticated worker.
     */
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
            'per_page' => $request->input('per_page', 20),
        ];

        $feed = $this->jobFeedService->getFeedForWorker($worker, $filters);

        return $this->successResponse($feed, 'Job feed retrieved successfully.');
    }
}
