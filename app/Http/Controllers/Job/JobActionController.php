<?php

namespace App\Http\Controllers\Job;

use App\Actions\Job\AcceptJobAction;
use App\Actions\Job\CancelJobAction;
use App\Actions\Job\CompleteJobAction;
use App\Actions\Job\InvestigateJobAction;
use App\Actions\Job\PrepareJobAction;
use App\Actions\Job\StartJobAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobActionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AcceptJobAction $acceptJobAction,
        private readonly InvestigateJobAction $investigateJobAction,
        private readonly PrepareJobAction $prepareJobAction,
        private readonly StartJobAction $startJobAction,
        private readonly CompleteJobAction $completeJobAction,
        private readonly CancelJobAction $cancelJobAction
    ) {}

    /**
     * Accept a job.
     */
    public function accept(Job $job, AcceptJobRequest $request): JsonResponse
    {
        $worker = $request->user()->worker;

        if (! $worker) {
            return $this->errorResponse('Worker profile not found.', 404);
        }

        $job = $this->acceptJobAction->execute($job, $worker);

        return $this->successResponse(
            new JobResource($job),
            'Job accepted successfully.'
        );
    }

    /**
     * Start investigating a job.
     */
    public function investigate(Job $job, Request $request): JsonResponse
    {
        try {
            $worker = $request->user()->worker;

            if (! $worker) {
                return $this->errorResponse('Worker profile not found.', 404);
            }

            $job = $this->investigateJobAction->execute($job, $worker);

            return $this->successResponse(
                new JobResource($job),
                'Job investigation started successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Start preparing for a job.
     */
    public function prepare(Job $job, Request $request): JsonResponse
    {
        try {
            $worker = $request->user()->worker;

            if (! $worker) {
                return $this->errorResponse('Worker profile not found.', 404);
            }

            $job = $this->prepareJobAction->execute($job, $worker);

            return $this->successResponse(
                new JobResource($job),
                'Job preparation started successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Start a job.
     */
    public function start(Job $job, Request $request): JsonResponse
    {
        $worker = $request->user()->worker;

        if (! $worker) {
            return $this->errorResponse('Worker profile not found.', 404);
        }

        $job = $this->startJobAction->execute($job, $worker);

        return $this->successResponse(
            new JobResource($job),
            'Job started successfully.'
        );
    }

    /**
     * Complete a job.
     */
    public function complete(Job $job, Request $request): JsonResponse
    {
        $worker = $request->user()->worker;

        if (! $worker) {
            return $this->errorResponse('Worker profile not found.', 404);
        }

        $job = $this->completeJobAction->execute($job, $worker);

        return $this->successResponse(
            new JobResource($job),
            'Job completed successfully.'
        );
    }

    /**
     * Cancel a job.
     */
    public function cancel(Job $job, Request $request): JsonResponse
    {
        $job = $this->cancelJobAction->execute($job, $request->user());

        return $this->successResponse(
            new JobResource($job),
            'Job cancelled successfully.'
        );
    }
}
