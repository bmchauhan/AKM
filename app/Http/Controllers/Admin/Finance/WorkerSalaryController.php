<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminWorkerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerSalaryController extends Controller
{
    public function __construct(
        private readonly AdminWorkerService $workers,
    ) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('finance.create') || $request->user()?->can('finance.update'), 403);

        $request->validate([
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'paid_on' => ['required', 'date'],
        ]);

        $payload = $this->workers->salaryLookup(
            $request->integer('worker_id'),
            $request->string('paid_on')->toString(),
        );

        if (! $payload) {
            return response()->json(['message' => __('messages.workers_salary_not_found')], 404);
        }

        return response()->json($payload);
    }
}
