<x-layouts.admin :pageTitle="__('messages.members_all')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.members') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.members_all') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.members_all_subtitle') }}</p>
            </div>

            @can('members.create')
                <x-common.button :href="route('admin.members.create', $canPickMainMember && $selectedMainMemberId ? ['main_member_id' => $selectedMainMemberId] : [])">
                    {{ __('messages.members_add') }}
                </x-common.button>
            @endcan
        </div>

        @if ($canPickMainMember)
            <form method="GET" action="{{ route('admin.members.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
                <x-common.select
                    name="main_member_id"
                    :label="__('messages.members_main_member')"
                    :options="collect($mainMembers)->map(fn ($m) => ['value' => $m['value'], 'label' => $m['label']])->all()"
                    :value="old('main_member_id', $selectedMainMemberId)"
                    required
                    onchange="this.form.submit()"
                >
                    <option value="">{{ __('messages.members_select_main_member') }}</option>
                </x-common.select>
            </form>
        @else
            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
                {{ __('messages.members_main_member_self', ['name' => auth()->user()->fullName()]) }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            @if ($canPickMainMember && ! $selectedMainMemberId)
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.members_select_main_member_hint') }}
                </div>
            @else
                <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-3">
                    <div class="md:col-span-3">{{ __('messages.users_name') }}</div>
                    <div class="md:col-span-2">{{ __('messages.users_mobile') }}</div>
                    <div class="md:col-span-2">{{ __('messages.users_house') }}</div>
                    <div class="md:col-span-2">{{ __('messages.members_type') }}</div>
                    @if ($canPickMainMember)
                        <div class="md:col-span-1">{{ __('messages.members_main_member') }}</div>
                    @endif
                    <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
                </div>

                @forelse ($members as $member)
                    <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-3">
                        <div class="mb-2 flex items-center gap-3 md:col-span-3 md:mb-0">
                            @if ($member['profile_image_url'])
                                <img src="{{ $member['profile_image_url'] }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#E6EBF4]" />
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E6EBF4] text-xs font-bold text-[#080D21]">
                                    {{ strtoupper(substr($member['name'], 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium text-[#0F141E]">{{ $member['name'] }}</span>
                        </div>
                        <div class="mb-2 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $member['mobile'] }}</div>
                        <div class="mb-2 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $member['house'] }}</div>
                        <div class="mb-2 md:col-span-2 md:mb-0">
                            <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                                {{ $member['membership_type'] }}
                            </span>
                        </div>
                        @if ($canPickMainMember)
                            <div class="mb-2 text-sm text-[#0F141E]/80 md:col-span-1 md:mb-0">{{ $member['main_member'] }}</div>
                        @endif
                        <div class="flex justify-end gap-2 md:col-span-2">
                            @can('members.update')
                                <a
                                    href="{{ route('admin.members.edit', $member['id']) }}"
                                    class="rounded-lg border border-[#E6EBF4] px-3 py-1.5 text-xs font-semibold text-[#080D21] transition hover:border-[#E6C280] hover:bg-[#ECEAE1]"
                                >
                                    {{ __('messages.users_update') }}
                                </a>
                            @endcan
                            @can('members.delete')
                                <form method="POST" action="{{ route('admin.members.destroy', $member['id']) }}" onsubmit="return confirm(@js(__('messages.members_delete_confirm')))">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="rounded-lg border border-[#E5989B]/40 px-3 py-1.5 text-xs font-semibold text-[#AB1E23] transition hover:bg-[#E5989B]/15"
                                    >
                                        {{ __('messages.users_remove') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                        {{ __('messages.members_empty') }}
                    </div>
                @endforelse
            @endif
        </div>
    </div>
</x-layouts.admin>
