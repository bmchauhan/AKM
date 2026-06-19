<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseUnit;
use App\Services\Admin\AdminHouseService;
use App\Services\Admin\HouseUnitSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseController extends Controller
{
    public function __construct(
        private readonly AdminHouseService $houses,
        private readonly HouseUnitSyncService $houseSync,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.houses.index', [
            'houses' => $this->houses->paginatedList([
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
                'status' => $request->query('status'),
            ]),
            'houseTypes' => $this->houseSync->houseTypesForSelect(),
            'filters' => [
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
                'status' => $request->query('status'),
            ],
        ]);
    }

    public function show(HouseUnit $house): View
    {
        $detail = $this->houses->detail($house);

        return view('admin.houses.show', [
            'house' => $detail['house'],
            'ownershipTimeline' => $detail['ownershipTimeline'],
            'financeByOwner' => $detail['financeByOwner'],
        ]);
    }

    public function transferHistory(Request $request): View
    {
        return view('admin.houses.transfer-history', [
            'transfers' => $this->houses->paginatedTransferHistory([
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
            ]),
            'houseTypes' => $this->houseSync->houseTypesForSelect(),
            'filters' => [
                'house_type' => $request->query('house_type'),
                'search' => $request->query('search'),
            ],
        ]);
    }
}
