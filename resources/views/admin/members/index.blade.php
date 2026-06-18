<x-layouts.admin :pageTitle="__('messages.members_all')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.members') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.members_all') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.members_all_subtitle') }}</p>
            </div>

            @can('members_add.create')
                @if ($canChooseHouseholdScope)
                    <div class="flex flex-wrap gap-2" x-data="{ tab: @js($activeHouseholdTab) }" x-on:members-tab-changed.window="tab = $event.detail.tab">
                        <x-common.button
                            x-show="tab === 'self'"
                            x-cloak
                            :href="route('admin.members.create', ['scope' => 'self'])"
                        >
                            {{ __('messages.members_add_for_self') }}
                        </x-common.button>
                        <x-common.button
                            x-show="tab === 'others'"
                            x-cloak
                            variant="secondary"
                            :href="route('admin.members.create', ['scope' => 'others'])"
                        >
                            {{ __('messages.members_add_for_others') }}
                        </x-common.button>
                    </div>
                @else
                    <x-common.button :href="route('admin.members.create')">
                        {{ __('messages.members_add') }}
                    </x-common.button>
                @endif
            @endcan
        </div>

        @if ($canChooseHouseholdScope)
            <div
                x-data="{
                    tab: @js($activeHouseholdTab),
                    setTab(next) {
                        this.tab = next;
                        window.dispatchEvent(new CustomEvent('members-tab-changed', { detail: { tab: next } }));
                    }
                }"
                class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm"
            >
                <div class="flex border-b border-[#E6EBF4] bg-[#ECEAE1]/50" role="tablist" aria-label="{{ __('messages.members_household_tabs') }}">
                    <button
                        type="button"
                        role="tab"
                        :id="'members-tab-self'"
                        :aria-selected="tab === 'self'"
                        @click="setTab('self')"
                        class="flex-1 px-4 py-3.5 text-sm font-semibold transition sm:flex-none sm:px-6"
                        :class="tab === 'self'
                            ? 'border-b-2 border-[#AB1E23] bg-white text-[#AB1E23]'
                            : 'text-[#0F141E]/60 hover:bg-[#E6EBF4]/40 hover:text-[#080D21]'"
                    >
                        {{ __('messages.members_my_household') }}
                    </button>
                    <button
                        type="button"
                        role="tab"
                        :id="'members-tab-others'"
                        :aria-selected="tab === 'others'"
                        @click="setTab('others')"
                        class="flex-1 px-4 py-3.5 text-sm font-semibold transition sm:flex-none sm:px-6"
                        :class="tab === 'others'
                            ? 'border-b-2 border-[#AB1E23] bg-white text-[#AB1E23]'
                            : 'text-[#0F141E]/60 hover:bg-[#E6EBF4]/40 hover:text-[#080D21]'"
                    >
                        {{ __('messages.members_tab_others') }}
                    </button>
                </div>

                <div class="p-4 sm:p-5">
                    <div
                        x-show="tab === 'self'"
                        x-cloak
                        role="tabpanel"
                        aria-labelledby="members-tab-self"
                        class="space-y-4"
                    >
                        <p class="text-sm text-[#0F141E]/70">
                            {{ __('messages.members_my_household_subtitle', ['name' => auth()->user()->fullName()]) }}
                            @if (auth()->user()->houseLabel())
                                <span class="text-[#0F141E]/50">· {{ auth()->user()->houseLabel() }}</span>
                            @endif
                        </p>

                        @include('admin.members._list', [
                            'members' => $ownHouseholdMembers,
                            'showMainMemberColumn' => false,
                            'emptyMessage' => __('messages.members_my_household_empty'),
                        ])
                    </div>

                    <div
                        x-show="tab === 'others'"
                        x-cloak
                        role="tabpanel"
                        aria-labelledby="members-tab-others"
                        class="space-y-4"
                    >
                        <p class="text-sm text-[#0F141E]/70">{{ __('messages.members_other_households_subtitle') }}</p>

                        <form method="POST" action="{{ route('admin.members.select-household') }}" class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/30 p-4 sm:p-5">
                            @csrf
                            <x-common.searchable-select
                                name="main_member_id"
                                :label="__('messages.members_main_member')"
                                :options="collect($mainMembers)->map(fn ($m) => ['value' => $m['value'], 'label' => $m['label']])->all()"
                                :value="$selectedMainMemberId"
                                :placeholder="__('messages.members_select_main_member')"
                                :submit-on-change="true"
                            />
                        </form>

                        @if ($selectedMainMemberId)
                            @include('admin.members._list', [
                                'members' => $othersMembers,
                                'showMainMemberColumn' => true,
                            ])
                        @else
                            <div class="rounded-xl border border-dashed border-[#E6EBF4] bg-[#ECEAE1]/40 px-4 py-10 text-center text-sm text-[#0F141E]/60">
                                {{ __('messages.members_select_main_member_hint') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @elseif ($canPickMainMember)
            <form method="POST" action="{{ route('admin.members.select-household') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
                @csrf
                <x-common.searchable-select
                    name="main_member_id"
                    :label="__('messages.members_main_member')"
                    :options="collect($mainMembers)->map(fn ($m) => ['value' => $m['value'], 'label' => $m['label']])->all()"
                    :value="$selectedMainMemberId"
                    :placeholder="__('messages.members_select_main_member')"
                    :submit-on-change="true"
                    required
                />
            </form>

            @if (! $selectedMainMemberId)
                <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.members_select_main_member_hint') }}
                </div>
            @else
                @include('admin.members._list', [
                    'members' => $members,
                    'showMainMemberColumn' => true,
                ])
            @endif
        @else
            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
                {{ __('messages.members_main_member_self', ['name' => auth()->user()->fullName()]) }}
            </div>

            @include('admin.members._list', [
                'members' => $members,
                'showMainMemberColumn' => false,
            ])
        @endif
    </div>
</x-layouts.admin>
