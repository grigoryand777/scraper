<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateJobRequest;
use App\Services\JobsService;
use Illuminate\Support\Facades\Redis;

class JobsController extends Controller
{
    /**
     * @var JobsService
     */
    private $jobsService;

    public function __construct(JobsService $jobsService, ) {
        $this->jobsService = $jobsService;
    }

    public function get(string $id) {
        return response()->json($this->jobsService->get($id));
    }

    public function create(CreateJobRequest $request) {
        return response()->json(['data'=> $this->jobsService->create($request->validated())]);
    }

    public function delete(string $id) {
        $this->jobsService->deleteJob($id);
        return response()->json("Successfully deleted job with id {$id}");
    }
}
