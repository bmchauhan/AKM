<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HouseTransferType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHouseTransferRequest;
use App\Models\HouseUnit;
use App\Services\Admin\HouseTransferService;
use App\Support\SwalDialog;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HouseTransferController extends Controller
{
    public function __construct(
        private readonly HouseTransferService $transfers,
    ) {}

    public function create(HouseUnit $house): View
    {
        $house->load('currentOwnership.mainMember');

        return view('admin.houses.transfer', [
            'house' => $house,
            'currentOwner' => $house->currentOwnership?->mainMember,
            'transferTypes' => HouseTransferType::cases(),
        ]);
    }

    public function store(StoreHouseTransferRequest $request, HouseUnit $house): RedirectResponse
    {
        try {
            $ownership = $this->transfers->transfer(
                $house,
                $request->user(),
                $request->validated(),
                $request->file('id_proof'),
                $request->file('profile_image'),
            );
        } catch (ValidationException $exception) {
            SwalDialog::alert([
                'title' => __('messages.houses_transfer_failed_title'),
                'message' => collect($exception->errors())->flatten()->first() ?? __('messages.houses_transfer_failed'),
                'icon' => 'warning',
                'confirm_text' => __('messages.swal_understood'),
            ]);

            return redirect()
                ->route('admin.houses.transfer.create', $house)
                ->withInput()
                ->withErrors($exception->errors());
        }

        Toast::success(__('messages.houses_transfer_success', [
            'house' => $house->label(),
            'owner' => $ownership->mainMember?->fullName() ?? '',
        ]));

        return redirect()->route('admin.houses.transfers.history');
    }
}
