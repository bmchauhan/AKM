<?php

namespace App\Http\Controllers\Admin\Workers;

use App\Http\Controllers\Admin\Workers\Concerns\OpensWorkerFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Workers\CreateWorkerLoginRequest;
use App\Http\Requests\Admin\Workers\DestroyWorkerRequest;
use App\Http\Requests\Admin\Workers\OpenEditWorkerRequest;
use App\Http\Requests\Admin\Workers\StoreWorkerRequest;
use App\Http\Requests\Admin\Workers\StoreWorkerSalaryRequest;
use App\Http\Requests\Admin\Workers\UpdateWorkerRequest;
use App\Models\Worker;
use App\Services\Admin\AdminWorkerService;
use App\Services\Admin\WorkerLoginService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkerController extends Controller
{
    use OpensWorkerFormModal;

    public function __construct(
        private readonly AdminWorkerService $workers,
        private readonly WorkerLoginService $workerLogins,
    ) {}

    public function index(Request $request): View
    {
        $tab = $this->workers->resolveTab($request->string('tab')->toString());
        $editingWorker = $this->workers->editingWorker();
        $loginCredentials = $this->workerLogins->pullFlashedCredentials();

        return view('admin.workers.index', [
            ...$this->workers->listForScreen($tab),
            'openAddWorkerModal' => $this->shouldOpenWorkerModal($request, 'add'),
            'openEditWorkerModal' => $editingWorker !== null || $this->shouldOpenWorkerModal($request, 'edit'),
            'openSalaryWorkerModal' => $this->shouldOpenWorkerModal($request, 'salary'),
            'openCredentialsModal' => filled($loginCredentials),
            'loginCredentials' => $loginCredentials,
            'editingWorker' => $editingWorker,
            'salaryHistory' => $editingWorker
                ? $this->workers->salaryHistoryForScreen($editingWorker)
                : [],
            'salaryWorkerId' => old('worker_id', $request->query('salary_worker')),
        ]);
    }

    public function store(StoreWorkerRequest $request): RedirectResponse
    {
        $this->workers->create(
            $request->user(),
            $request->validated(),
            $request->file('profile_image'),
        );

        Toast::success(__('messages.workers_created'));

        return redirect()->route('admin.workers.index', ['tab' => $request->input('worker_type')]);
    }

    public function createLogin(CreateWorkerLoginRequest $request): RedirectResponse
    {
        $worker = Worker::query()->findOrFail($request->integer('worker_id'));

        $result = $this->workerLogins->createLoginProfile($worker, $request->user());
        $this->workerLogins->rememberCredentials($worker, $result['username'], $result['plain_password']);

        Toast::success(__('messages.workers_login_created'));

        return redirect()->route('admin.workers.index', ['tab' => $worker->worker_type->value]);
    }

    public function openEdit(OpenEditWorkerRequest $request): RedirectResponse
    {
        $worker = Worker::query()->findOrFail($request->integer('worker_id'));
        $this->workers->rememberEditingWorker($worker);

        return redirect()->route('admin.workers.index', ['tab' => $worker->worker_type->value]);
    }

    public function cancelEdit(): RedirectResponse
    {
        $worker = $this->workers->editingWorker();
        $tab = $worker?->worker_type->value;
        $this->workers->clearEditingWorker();

        return redirect()->route('admin.workers.index', array_filter(['tab' => $tab]));
    }

    public function update(UpdateWorkerRequest $request): RedirectResponse
    {
        $worker = $this->workers->editingWorker();

        if (! $worker) {
            return redirect()->route('admin.workers.index');
        }

        $this->workers->update($worker, $request->validated(), $request->file('profile_image'));
        $this->workers->clearEditingWorker();

        Toast::success(__('messages.workers_updated'));

        return redirect()->route('admin.workers.index', ['tab' => $worker->worker_type->value]);
    }

    public function storeSalary(StoreWorkerSalaryRequest $request): RedirectResponse
    {
        $worker = Worker::query()->findOrFail($request->integer('worker_id'));

        $this->workers->addSalaryRate($worker, $request->user(), [
            'monthly_salary' => $request->input('monthly_salary'),
            'effective_from' => $request->input('effective_from'),
            'notes' => $request->input('notes'),
        ]);

        Toast::success(__('messages.workers_salary_saved'));

        return redirect()->route('admin.workers.index', ['tab' => $worker->worker_type->value]);
    }

    public function destroy(DestroyWorkerRequest $request): RedirectResponse
    {
        $worker = Worker::query()->findOrFail($request->integer('worker_id'));
        $tab = $worker->worker_type->value;

        $this->workers->delete($worker);

        if ((int) session(AdminWorkerService::SESSION_EDITING_WORKER) === $worker->id) {
            $this->workers->clearEditingWorker();
        }

        Toast::success(__('messages.workers_deleted'));

        return redirect()->route('admin.workers.index', ['tab' => $tab]);
    }
}
