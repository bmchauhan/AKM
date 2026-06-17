<?php

namespace App\Services\Admin;

use App\Enums\WorkerType;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSalaryRate;
use App\Traits\HandlesUploads;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class AdminWorkerService
{
    use HandlesUploads;

    public const SESSION_EDITING_WORKER = 'admin.workers.editing_worker_id';

    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    /**
     * @return list<array{value: string, label: string}>
     */
    public function workerTypesForSelect(): array
    {
        return collect(WorkerType::cases())
            ->map(fn (WorkerType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->all();
    }

    /**
     * @return array{
     *     workers: LengthAwarePaginator,
     *     activeTab: string,
     *     workerTypes: list<array{value: string, label: string}>
     * }
     */
    public function listForScreen(string $tab): array
    {
        $activeTab = $this->resolveTab($tab);

        $workers = Worker::query()
            ->where('worker_type', $activeTab)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $workers->getCollection()->transform(fn (Worker $worker) => $this->mapListRow($worker));

        return [
            'workers' => $workers,
            'activeTab' => $activeTab,
            'workerTypes' => $this->workerTypesForSelect(),
        ];
    }

    public function resolveTab(string $tab): string
    {
        $valid = array_column(WorkerType::cases(), 'value');

        return in_array($tab, $valid, true)
            ? $tab
            : WorkerType::SecurityGuard->value;
    }

    /**
     * @param  array{
     *     worker_type: string,
     *     name: string,
     *     mobile_number?: ?string,
     *     address?: ?string,
     *     joined_on?: ?string,
     *     notes?: ?string,
     *     monthly_salary: float|int|string,
     *     salary_effective_from?: ?string
     * }  $data
     */
    public function create(User $actor, array $data, ?UploadedFile $profileImage = null): Worker
    {
        $worker = Worker::query()->create([
            'worker_type' => $data['worker_type'],
            'name' => $data['name'],
            'mobile_number' => $data['mobile_number'] ?? null,
            'address' => $data['address'] ?? null,
            'joined_on' => $data['joined_on'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
            'profile_image_path' => $profileImage
                ? $this->storePublicUpload($profileImage, 'workers/profile-images')
                : null,
        ]);

        $this->addSalaryRate($worker, $actor, [
            'monthly_salary' => $data['monthly_salary'],
            'effective_from' => $data['salary_effective_from'] ?? ($data['joined_on'] ?? now()->toDateString()),
            'notes' => __('messages.workers_initial_salary_note'),
        ]);

        return $worker;
    }

    /**
     * @param  array{
     *     name: string,
     *     mobile_number?: ?string,
     *     address?: ?string,
     *     joined_on?: ?string,
     *     notes?: ?string,
     *     is_active?: bool
     * }  $data
     */
    public function update(Worker $worker, array $data, ?UploadedFile $profileImage = null): Worker
    {
        $payload = [
            'name' => $data['name'],
            'mobile_number' => $data['mobile_number'] ?? null,
            'address' => $data['address'] ?? null,
            'joined_on' => $data['joined_on'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if ($profileImage) {
            $this->deletePublicUpload($worker->profile_image_path);
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'workers/profile-images');
        }

        $worker->update($payload);

        return $worker->fresh();
    }

    /**
     * @param  array{monthly_salary: float|int|string, effective_from: string, notes?: ?string}  $data
     */
    public function addSalaryRate(Worker $worker, User $actor, array $data): WorkerSalaryRate
    {
        return WorkerSalaryRate::query()->create([
            'worker_id' => $worker->id,
            'monthly_salary' => $data['monthly_salary'],
            'effective_from' => $data['effective_from'],
            'notes' => $data['notes'] ?? null,
            'set_by_user_id' => $actor->id,
        ]);
    }

    public function salaryOnDate(Worker $worker, string|Carbon $onDate): ?WorkerSalaryRate
    {
        $date = $onDate instanceof Carbon ? $onDate->toDateString() : $onDate;

        return WorkerSalaryRate::query()
            ->where('worker_id', $worker->id)
            ->whereDate('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->first();
    }

    public function salaryLookup(int $workerId, string $paidOn): ?array
    {
        $worker = Worker::query()->find($workerId);

        if (! $worker || ! $worker->is_active) {
            return null;
        }

        $rate = $this->salaryOnDate($worker, $paidOn);

        if (! $rate) {
            return null;
        }

        return [
            'worker_id' => $worker->id,
            'worker_name' => $worker->name,
            'monthly_salary' => (float) $rate->monthly_salary,
            'formatted_salary' => $this->fundSettings->formatMoney($rate->monthly_salary),
            'effective_from' => $rate->effective_from->format('d M Y'),
        ];
    }

    /**
     * @return list<array{value: int, label: string, worker_type: string}>
     */
    public function activeWorkersForExpenseSelect(?WorkerType $type = null): array
    {
        return Worker::query()
            ->when($type, fn ($query) => $query->where('worker_type', $type->value))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Worker $worker) => [
                'value' => $worker->id,
                'label' => $worker->name,
                'worker_type' => $worker->worker_type->value,
            ])
            ->all();
    }

    /**
     * @return array<string, list<array{value: int, label: string, worker_type: string}>>
     */
    public function activeWorkersGroupedForExpense(): array
    {
        $grouped = [];

        foreach (WorkerType::cases() as $type) {
            $grouped[$type->value] = $this->activeWorkersForExpenseSelect($type);
        }

        return $grouped;
    }

    public function rememberEditingWorker(Worker $worker): void
    {
        session([self::SESSION_EDITING_WORKER => $worker->id]);
    }

    public function editingWorker(): ?Worker
    {
        $workerId = session(self::SESSION_EDITING_WORKER);

        if (! $workerId) {
            return null;
        }

        return Worker::query()
            ->with(['salaryRates' => fn ($query) => $query->orderByDesc('effective_from')->limit(5)])
            ->find((int) $workerId);
    }

    public function clearEditingWorker(): void
    {
        session()->forget(self::SESSION_EDITING_WORKER);
    }

    public function delete(Worker $worker): void
    {
        $this->deletePublicUpload($worker->profile_image_path);
        $worker->delete();
    }

    /**
     * @return list<array{monthly_salary: string, effective_from: string, notes: ?string, set_by: string}>
     */
    public function salaryHistoryForScreen(Worker $worker, int $limit = 5): array
    {
        return $worker->salaryRates()
            ->with('setBy')
            ->orderByDesc('effective_from')
            ->limit($limit)
            ->get()
            ->map(fn (WorkerSalaryRate $rate) => [
                'monthly_salary' => $this->fundSettings->formatMoney($rate->monthly_salary),
                'effective_from' => $rate->effective_from->format('d M Y'),
                'notes' => $rate->notes,
                'set_by' => $rate->setBy?->fullName() ?? '—',
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListRow(Worker $worker): array
    {
        $currentRate = $this->salaryOnDate($worker, now());

        return [
            'id' => $worker->id,
            'name' => $worker->name,
            'mobile' => $worker->mobile_number ?? '—',
            'profile_image_url' => $worker->profileImageUrl(),
            'is_active' => $worker->is_active,
            'current_salary' => $currentRate
                ? $this->fundSettings->formatMoney($currentRate->monthly_salary)
                : '—',
            'salary_effective_from' => $currentRate?->effective_from->format('d M Y'),
            'joined_on' => $worker->joined_on?->format('d M Y') ?? '—',
        ];
    }
}
