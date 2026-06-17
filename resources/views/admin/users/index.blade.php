@php
    $houseTypeOptions = collect(\App\Enums\HouseType::cases())->map(fn ($type) => [
        'value' => $type->value,
        'label' => $type->label(),
    ])->all();
    $hasActiveFilters = collect($filters)->filter()->isNotEmpty();
@endphp

<x-layouts.admin :pageTitle="__('messages.users_all')">
    <div
        class="space-y-6"
        x-data="usersList({
            householdUrl: @js(route('admin.users.household-members')),
            emptyMessage: @js(__('messages.users_household_members_empty')),
            loadingMessage: @js(__('messages.users_household_loading')),
        })"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.users') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.users_all') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.users_all_subtitle') }}</p>
            </div>

            @can('users.create')
                <x-common.button :href="route('admin.users.create')">
                    {{ __('messages.users_add') }}
                </x-common.button>
            @endcan
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
            <div class="grid gap-4 md:grid-cols-12 md:items-end">
                <div class="md:col-span-3">
                    <x-common.input
                        name="name"
                        :label="__('messages.users_name')"
                        :value="$filters['name']"
                        :placeholder="__('messages.users_filter_name_placeholder')"
                    />
                </div>
                <div class="md:col-span-2">
                    <x-common.select
                        name="house_type"
                        :label="__('messages.users_house_type')"
                        :options="$houseTypeOptions"
                        :value="$filters['house_type']"
                    >
                        <option value="">{{ __('messages.users_filter_all') }}</option>
                    </x-common.select>
                </div>
                <div class="md:col-span-2">
                    <x-common.input
                        name="house_number"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        :label="__('messages.users_house_number')"
                        :placeholder="__('messages.users_house_number_placeholder')"
                        :value="$filters['house_number']"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    />
                </div>
                <div class="md:col-span-2">
                    <x-common.select
                        name="role"
                        :label="__('messages.users_filter_member_type')"
                        :options="$roleOptions"
                        :value="$filters['role']"
                    >
                        <option value="">{{ __('messages.users_filter_all') }}</option>
                    </x-common.select>
                </div>
                <div class="flex flex-wrap gap-2 md:col-span-3">
                    <x-common.button type="submit" class="min-w-[7rem]">
                        {{ __('messages.users_filter_apply') }}
                    </x-common.button>
                    @if ($hasActiveFilters)
                        <x-common.button type="button" variant="secondary" :href="route('admin.users.index')">
                            {{ __('messages.users_filter_clear') }}
                        </x-common.button>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-4">{{ __('messages.users_name') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_mobile') }}</div>
                <div class="md:col-span-1">{{ __('messages.users_house') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_role') }}</div>
                <div class="md:col-span-1">{{ __('messages.users_gender') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($users as $user)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-2 flex items-center gap-3 md:col-span-4 md:mb-0">
                        @if ($user['profile_image_url'])
                            <img src="{{ $user['profile_image_url'] }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#E6EBF4]" />
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E6EBF4] text-xs font-bold text-[#080D21]">
                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-sm font-medium text-[#0F141E]">{{ $user['name'] }}</span>
                    </div>
                    <div class="mb-2 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $user['mobile'] }}</div>
                    <div class="mb-2 whitespace-nowrap text-sm text-[#0F141E] md:col-span-1 md:mb-0">{{ $user['house'] }}</div>
                    <div class="mb-2 md:col-span-2 md:mb-0">
                        <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ $user['role'] }}
                        </span>
                    </div>
                    <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-1 md:mb-0">{{ $user['gender'] }}</div>
                    <div class="flex items-center justify-end gap-1 md:col-span-2">
                        @if ($user['is_main_member'])
                            <x-common.icon-action
                                type="button"
                                variant="accent"
                                :title="__('messages.users_view_members')"
                                x-on:click="openHousehold({{ $user['id'] }})"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                @if ($user['household_count'] > 0)
                                    <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#AB1E23] px-1 text-[10px] font-bold leading-none text-[#E6EBF4]">
                                        {{ $user['household_count'] }}
                                    </span>
                                @endif
                            </x-common.icon-action>
                        @endif
                        @if ($user['can_edit'] && (! $user['is_super_admin'] || auth()->user()->can('super-admin')))
                            <form
                                method="POST"
                                action="{{ $user['is_household_member'] ? route('admin.members.open-edit') : route('admin.users.open-edit') }}"
                                class="inline-flex"
                            >
                                @csrf
                                <input type="hidden" name="{{ $user['is_household_member'] ? 'member_id' : 'user_id' }}" value="{{ $user['id'] }}">
                                <x-common.icon-action
                                    type="submit"
                                    :title="$user['is_household_member'] ? __('messages.members_update') : __('messages.users_update')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </x-common.icon-action>
                            </form>
                        @endif
                        @can('users.delete')
                            @unless ($user['is_super_admin'])
                            <form method="POST" action="{{ route('admin.users.destroy') }}" class="inline-flex" onsubmit="return confirm(@js(__('messages.users_delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                <x-common.icon-action
                                    type="submit"
                                    variant="danger"
                                    :title="__('messages.users_remove')"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </x-common.icon-action>
                            </form>
                            @endunless
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ $hasActiveFilters ? __('messages.users_empty_filtered') : __('messages.users_empty') }}
                </div>
            @endforelse

            <x-common.pagination :paginator="$users" class="border-t border-[#E6EBF4]" />
        </div>

        <x-common.modal maxWidth="max-w-3xl">
                <template x-if="modalLoading">
                    <p class="py-8 text-center text-sm text-[#0F141E]/60" x-text="loadingMessage"></p>
                </template>

                <template x-if="! modalLoading">
                    <div class="space-y-5">
                        <template x-if="modalMainMember">
                            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#AB1E23]">{{ __('messages.members_main_member') }}</p>
                                <p class="mt-1 text-sm font-bold text-[#080D21]" x-text="modalMainMember.name"></p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#0F141E]/80">
                                    <span x-text="modalMainMember.house"></span>
                                    <span x-text="modalMainMember.mobile"></span>
                                    <span x-text="modalMainMember.role"></span>
                                </div>
                            </div>
                        </template>

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.users_household_members') }}</p>

                            <template x-if="householdMembers.length === 0">
                                <p class="rounded-xl border border-dashed border-[#E6EBF4] px-4 py-8 text-center text-sm text-[#0F141E]/60" x-text="emptyMessage"></p>
                            </template>

                            <template x-if="householdMembers.length > 0">
                                <div class="overflow-hidden rounded-xl border border-[#E6EBF4]">
                                    <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-[#080D21] sm:grid sm:grid-cols-12 sm:gap-3">
                                        <div class="sm:col-span-4">{{ __('messages.users_name') }}</div>
                                        <div class="sm:col-span-3">{{ __('messages.users_mobile') }}</div>
                                        <div class="sm:col-span-3">{{ __('messages.members_type') }}</div>
                                        <div class="sm:col-span-2">{{ __('messages.users_gender') }}</div>
                                    </div>
                                    <template x-for="member in householdMembers" :key="member.id">
                                        <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 sm:grid sm:grid-cols-12 sm:items-center sm:gap-3">
                                            <div class="mb-1 text-sm font-medium text-[#0F141E] sm:col-span-4 sm:mb-0" x-text="member.name"></div>
                                            <div class="mb-1 text-sm text-[#0F141E] sm:col-span-3 sm:mb-0" x-text="member.mobile"></div>
                                            <div class="mb-1 sm:col-span-3 sm:mb-0">
                                                <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]" x-text="member.role"></span>
                                            </div>
                                            <div class="text-sm text-[#0F141E]/70 sm:col-span-2" x-text="member.gender"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
        </x-common.modal>
    </div>
</x-layouts.admin>
