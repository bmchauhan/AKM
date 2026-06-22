<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Visitors\CheckoutVisitorEntryRequest;
use App\Http\Requests\Admin\Visitors\DestroyVisitorEntryRequest;
use App\Http\Requests\Admin\Visitors\HouseHostsRequest;
use App\Http\Requests\Admin\Visitors\StoreVisitorEntryRequest;
use App\Models\HouseUnit;
use App\Models\VisitorEntry;
use App\Services\Admin\AdminVisitorEntryService;
use App\Services\Admin\HouseUnitSyncService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorEntryController extends Controller
{
    public function __construct(
        private readonly AdminVisitorEntryService $visitors,
        private readonly HouseUnitSyncService $houseSync,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.visitors.index', [
            'entries' => $this->visitors->paginatedList([
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
                'host_type' => $request->query('host_type'),
                'status' => $request->query('status'),
                'rental_only' => $request->boolean('rental_only'),
                'females_only' => $request->boolean('females_only'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ]),
            'houseTypes' => $this->houseSync->houseTypesForSelect(),
            'filters' => [
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
                'host_type' => $request->query('host_type'),
                'status' => $request->query('status'),
                'rental_only' => $request->boolean('rental_only'),
                'females_only' => $request->boolean('females_only'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
        ]);
    }

    public function show(VisitorEntry $visitorEntry): View
    {
        $visitorEntry->load(['houseUnit', 'host', 'loggedBy']);

        return view('admin.visitors.show', [
            'entry' => $visitorEntry,
        ]);
    }

    public function log(): View
    {
        return view('admin.visitors.log', [
            'houses' => $this->visitors->occupiedHousesForSelect(),
        ]);
    }

    public function today(): View
    {
        return view('admin.visitors.today', [
            'entries' => $this->visitors->todaysActive(),
        ]);
    }

    public function houseHosts(HouseHostsRequest $request): JsonResponse
    {
        $house = HouseUnit::query()->findOrFail($request->integer('house_unit_id'));

        return response()->json([
            'hosts' => $this->visitors->hostsForHouse($house)->values(),
        ]);
    }

    public function store(StoreVisitorEntryRequest $request): RedirectResponse
    {
        $this->visitors->create(
            $request->user(),
            $request->entryData(),
            $request->file('id_proof'),
            $request->file('photo'),
        );

        Toast::success(__('messages.visitors_created'));

        return redirect()->route('admin.visitors.today');
    }

    public function checkout(CheckoutVisitorEntryRequest $request): RedirectResponse
    {
        $this->visitors->checkout($request->visitorEntry());

        Toast::success(__('messages.visitors_checked_out'));

        return redirect()->route('admin.visitors.today');
    }

    public function destroy(DestroyVisitorEntryRequest $request): RedirectResponse
    {
        $entry = VisitorEntry::query()->findOrFail($request->integer('visitor_entry_id'));
        $this->visitors->delete($entry);

        Toast::success(__('messages.visitors_deleted'));

        return redirect()->route('admin.visitors.index');
    }
}
