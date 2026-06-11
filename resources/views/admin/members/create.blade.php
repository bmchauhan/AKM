<x-layouts.admin :pageTitle="__('messages.members_add')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.members') }}</p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.members_add') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.members_add_subtitle') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-6" autocomplete="off" novalidate>
            @csrf
            @include('admin.members._form', [
                'mainMembers' => $mainMembers,
                'membershipTypes' => $membershipTypes,
                'defaultMainMemberId' => $defaultMainMemberId ?? request('main_member_id'),
                'canPickMainMember' => $canPickMainMember,
            ])

            <div class="mt-8 flex flex-wrap gap-3 border-t border-[#E6EBF4] pt-6">
                <x-common.button type="submit">{{ __('messages.members_save') }}</x-common.button>
                <x-common.button type="button" variant="secondary" :href="route('admin.members.index')">
                    {{ __('messages.users_cancel') }}
                </x-common.button>
            </div>
        </form>
    </div>
</x-layouts.admin>
