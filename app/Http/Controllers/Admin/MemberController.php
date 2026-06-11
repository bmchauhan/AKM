<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipRole;
use App\Http\Controllers\Controller;
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

    public function index(Request $request): View
    {
        $actor = auth()->user();
        $mainMemberId = $request->integer('main_member_id') ?: $this->members->defaultMainMemberId($actor);

        return view('admin.members.index', [
            'members' => $this->members->listForScreen($actor, $mainMemberId),
            'mainMembers' => $this->members->mainMembersForSelect($actor),
            'selectedMainMemberId' => $mainMemberId,
            'canPickMainMember' => $this->members->canPickMainMember($actor),
            'membershipTypes' => $this->members->membershipTypesForSelect(),
        ]);
    }

    public function create(): View
    {
        $actor = auth()->user();

        return view('admin.members.create', [
            'mainMembers' => $this->members->mainMembersForSelect($actor),
            'defaultMainMemberId' => $this->members->defaultMainMemberId($actor)
                ?? (request()->integer('main_member_id') ?: null),
            'canPickMainMember' => $this->members->canPickMainMember($actor),
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

        Toast::success(__('messages.members_created'));

        $redirectParams = $actor->isMainMember()
            ? []
            : ['main_member_id' => $request->integer('linked_main_member_id')];

        return redirect()->route('admin.members.index', $redirectParams);
    }

    public function edit(User $member): View|RedirectResponse
    {
        $this->ensureHouseholdMember($member);
        $this->members->assertCanManage(auth()->user(), $member);

        $actor = auth()->user();

        return view('admin.members.edit', [
            'member' => $member->load('mainMember'),
            'mainMembers' => $this->members->mainMembersForSelect($actor),
            'defaultMainMemberId' => $member->linked_main_member_id,
            'canPickMainMember' => $this->members->canPickMainMember($actor),
            'membershipTypes' => $this->members->membershipTypesForSelect(),
        ]);
    }

    public function update(UpdateMemberRequest $request, User $member): RedirectResponse
    {
        $this->ensureHouseholdMember($member);

        $actor = auth()->user();

        $this->members->update(
            $actor,
            $member,
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        Toast::success(__('messages.members_updated'));

        $redirectParams = $actor->isMainMember()
            ? []
            : ['main_member_id' => $member->linked_main_member_id];

        return redirect()->route('admin.members.index', $redirectParams);
    }

    public function destroy(User $member): RedirectResponse
    {
        $this->ensureHouseholdMember($member);

        $actor = auth()->user();
        $mainMemberId = $member->linked_main_member_id;

        $this->members->delete($member, $actor);

        Toast::success(__('messages.members_deleted'));

        $redirectParams = $actor->isMainMember()
            ? []
            : ['main_member_id' => $mainMemberId];

        return redirect()->route('admin.members.index', $redirectParams);
    }

    private function ensureHouseholdMember(User $member): void
    {
        if (! in_array($member->role, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            abort(404);
        }
    }
}
