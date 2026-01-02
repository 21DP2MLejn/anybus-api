<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwipeJobRequest;
use App\Models\Job;
use App\Services\Job\SwipeJobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SwipeJobController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SwipeJobService $swipeJobService
    ) {}

    /**
     * Swipe on a job (like or skip).
     */
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
