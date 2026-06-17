<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\FinanceCollectionType;
use App\Http\Controllers\Admin\Finance\Concerns\OpensFinanceFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\DestroyCollectionRequest;
use App\Http\Requests\Admin\Finance\OpenEditCollectionRequest;
use App\Http\Requests\Admin\Finance\StoreCollectionRequest;
use App\Http\Requests\Admin\Finance\UpdateCollectionRequest;
use App\Models\FinanceCollection;
use App\Services\Admin\AdminFinanceCollectionService;
use App\Services\Admin\AdminFinanceMaintenanceChargeService;
use App\Services\Admin\AdminFinanceExportService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollectionController extends Controller
{
    use OpensFinanceFormModal;

    public function __construct(
        private readonly AdminFinanceCollectionService $collections,
        private readonly AdminFinanceExportService $exports,
        private readonly AdminFinanceMaintenanceChargeService $maintenanceCharges,
    ) {}

    public function index(Request $request): View
    {
        $defaultType = $request->query('type');
        if (! in_array($defaultType, array_column(FinanceCollectionType::cases(), 'value'), true)) {
            $defaultType = FinanceCollectionType::ClubhouseBooking->value;
        }

        $editingCollection = $this->collections->editingCollection();
        $screen = $this->collections->listForScreen([
            'collection_type' => $request->string('collection_type')->toString(),
            'main_member_id' => $request->string('main_member_id')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ]);

        return view('admin.finance.collections.index', [
            ...$screen,
            'editCollectionTypes' => $this->collections->collectionTypesForEdit($editingCollection),
            'defaultCollectionType' => $defaultType,
            'openCollectionModal' => $this->shouldOpenFinanceModal($request, 'collection'),
            'editingCollection' => $editingCollection,
            'openEditCollectionModal' => $editingCollection !== null
                || $this->shouldOpenFinanceModal($request, 'collection-edit'),
            'maintenanceChargeLookupUrl' => route('admin.finance.maintenance-charge.show'),
        ]);
    }

    public function store(StoreCollectionRequest $request): RedirectResponse
    {
        $this->collections->create($request->user(), $request->validated());

        Toast::success(__('messages.finance_collection_created'));

        return back();
    }

    public function openEdit(OpenEditCollectionRequest $request): RedirectResponse
    {
        $collection = FinanceCollection::query()->findOrFail($request->integer('collection_id'));
        $this->collections->rememberEditingCollection($collection);

        return redirect()->route('admin.finance.collections.index');
    }

    public function cancelEdit(): RedirectResponse
    {
        $this->collections->clearEditingCollection();

        return redirect()->route('admin.finance.collections.index');
    }

    public function update(UpdateCollectionRequest $request): RedirectResponse
    {
        $collectionId = $request->editingCollectionId();

        if (! $collectionId) {
            return redirect()->route('admin.finance.collections.index');
        }

        $collection = FinanceCollection::query()->findOrFail($collectionId);
        $this->collections->update($collection, $request->validated());
        $this->collections->clearEditingCollection();

        Toast::success(__('messages.finance_collection_updated'));

        return redirect()->route('admin.finance.collections.index');
    }

    public function destroy(DestroyCollectionRequest $request): RedirectResponse
    {
        $collection = FinanceCollection::query()->findOrFail($request->integer('collection_id'));
        $this->collections->delete($collection);

        if ((int) session(AdminFinanceCollectionService::SESSION_EDITING_COLLECTION) === $collection->id) {
            $this->collections->clearEditingCollection();
        }

        Toast::success(__('messages.finance_collection_deleted'));

        return back();
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->exports->collectionsCsv([
            'collection_type' => $request->string('collection_type')->toString(),
            'main_member_id' => $request->string('main_member_id')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ]);
    }
}
