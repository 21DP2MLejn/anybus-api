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

class JobActionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JobTransitionCommand $jobTransitionCommand
    ) {}

    /**
     * Accept a job.
     */
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

    /**
     * Start investigating a job.
     */
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

    /**
     * Start preparing for a job.
     */
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

    /**
     * Start a job.
     */
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

    /**
     * Complete a job.
     */
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

    /**
     * Cancel a job.
     */
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
