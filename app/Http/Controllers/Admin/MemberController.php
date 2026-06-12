<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyMemberRequest;
use App\Http\Requests\Admin\OpenMemberEditRequest;
use App\Http\Requests\Admin\SelectHouseholdRequest;
use App\Http\Requests\Admin\StoreMemberRequest;
use App\Http\Requests\Admin\UpdateMemberRequest;
use App\Models\User;
use App\Services\Admin\AdminMemberService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly AdminMemberService $members,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $actor = auth()->user();

        if ($request->has('main_member_id')) {
            if ($request->filled('main_member_id') && $this->members->canPickMainMember($actor)) {
                $this->members->rememberSelectedMainMember($actor, $request->integer('main_member_id'));
            }

            $redirectParams = $this->members->canChooseHouseholdScope($actor)
                ? ['tab' => 'others']
                : [];

            return redirect()->route('admin.members.index', $redirectParams);
        }

        $mainMemberId = $this->members->selectedMainMemberId($actor);
        $canChooseHouseholdScope = $this->members->canChooseHouseholdScope($actor);
        $othersMainMemberId = $this->members->othersMainMemberId($actor, $mainMemberId);

        $activeHouseholdTab = in_array($request->query('tab'), ['self', 'others'], true)
            ? $request->query('tab')
            : ($othersMainMemberId ? 'others' : 'self');

        return view('admin.members.index', [
            'members' => $canChooseHouseholdScope
                ? []
                : $this->members->listForScreen($actor, $mainMemberId),
            'ownHouseholdMembers' => $canChooseHouseholdScope
                ? $this->members->listForScreen($actor, $actor->id)
                : [],
            'othersMembers' => $canChooseHouseholdScope && $othersMainMemberId
                ? $this->members->listForScreen($actor, $othersMainMemberId)
                : [],
            'mainMembers' => $this->members->mainMembersForOthersSelect($actor),
            'selectedMainMemberId' => $othersMainMemberId ?? $mainMemberId,
            'activeHouseholdTab' => $activeHouseholdTab,
            'canPickMainMember' => $this->members->canPickMainMember($actor),
            'canChooseHouseholdScope' => $canChooseHouseholdScope,
            'membershipTypes' => $this->members->membershipTypesForSelect(),
        ]);
    }

    public function selectHousehold(SelectHouseholdRequest $request): RedirectResponse
    {
        $actor = auth()->user();
        $this->members->rememberSelectedMainMember($actor, $request->integer('main_member_id'));

        $redirectParams = $this->members->canChooseHouseholdScope($actor)
            ? ['tab' => 'others']
            : [];

        return redirect()->route('admin.members.index', $redirectParams);
    }

    public function create(Request $request): View
    {
        $actor = auth()->user();
        $canChooseHouseholdScope = $this->members->canChooseHouseholdScope($actor);
        $householdScope = 'self';

        if ($canChooseHouseholdScope && $request->query('scope') === 'others') {
            $householdScope = 'others';
        }

        $othersMainMemberId = $this->members->othersMainMemberId(
            $actor,
            $this->members->selectedMainMemberId($actor),
        );

        return view('admin.members.create', [
            'mainMembers' => $householdScope === 'others'
                ? $this->members->mainMembersForOthersSelect($actor)
                : $this->members->mainMembersForSelect($actor),
            'defaultMainMemberId' => $householdScope === 'others'
                ? ($othersMainMemberId ?? $actor->id)
                : $actor->id,
            'defaultHouseholdScope' => $householdScope,
            'canPickMainMember' => $this->members->canPickMainMember($actor),
            'canChooseHouseholdScope' => $canChooseHouseholdScope,
            'membershipTypes' => $this->members->membershipTypesForSelect(),
        ]);
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $actor = auth()->user();

        $this->members->create(
            $actor,
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        if ($this->members->canPickMainMember($actor)) {
            $this->members->rememberSelectedMainMember($actor, $request->integer('linked_main_member_id'));
        }

        Toast::success(__('messages.members_created'));

        return redirect()->route('admin.members.index');
    }

    public function openEdit(OpenMemberEditRequest $request): RedirectResponse
    {
        $member = User::query()->findOrFail($request->integer('member_id'));

        $this->ensureHouseholdMember($member);
        $this->members->assertCanManage(auth()->user(), $member);
        $this->members->rememberEditingMember($member);

        return redirect()->route('admin.members.edit');
    }

    public function edit(): View|RedirectResponse
    {
        $member = $this->members->editingMember();

        if (! $member) {
            return redirect()->route('admin.members.index');
        }

        $this->ensureHouseholdMember($member);
        $this->members->assertCanManage(auth()->user(), $member);

        $actor = auth()->user();

        return view('admin.members.edit', [
            'member' => $member->load('mainMember'),
            'mainMembers' => $this->members->mainMembersForSelect($actor),
            'defaultMainMemberId' => $member->linked_main_member_id,
            'canPickMainMember' => $this->members->canPickMainMember($actor),
            'canChooseHouseholdScope' => $this->members->canChooseHouseholdScope($actor),
            'membershipTypes' => $this->members->membershipTypesForSelect(),
        ]);
    }

    public function update(UpdateMemberRequest $request): RedirectResponse
    {
        $member = $this->members->editingMember();

        if (! $member) {
            return redirect()->route('admin.members.index');
        }

        $this->ensureHouseholdMember($member);

        $actor = auth()->user();

        $this->members->update(
            $actor,
            $member,
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        if ($this->members->canPickMainMember($actor)) {
            $this->members->rememberSelectedMainMember($actor, (int) $member->linked_main_member_id);
        }

        $this->members->clearEditingMember();

        Toast::success(__('messages.members_updated'));

        return redirect()->route('admin.members.index');
    }

    public function destroy(DestroyMemberRequest $request): RedirectResponse
    {
        $member = User::query()->findOrFail($request->integer('member_id'));

        $this->ensureHouseholdMember($member);

        $actor = auth()->user();
        $mainMemberId = $member->linked_main_member_id;

        $this->members->delete($member, $actor);

        if ($this->members->canPickMainMember($actor) && $mainMemberId) {
            $this->members->rememberSelectedMainMember($actor, (int) $mainMemberId);
        }

        Toast::success(__('messages.members_deleted'));

        return redirect()->route('admin.members.index');
    }

    private function ensureHouseholdMember(User $member): void
    {
        if (! in_array($member->membership_type, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            abort(404);
        }
    }
}
