@props([
    'members' => [],
    'showMainMemberColumn' => false,
    'emptyMessage' => null,
])

@php
    $emptyMessage = $emptyMessage ?? __('messages.members_empty');
@endphp

<div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
    <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
        <div class="{{ $showMainMemberColumn ? 'md:col-span-3' : 'md:col-span-4' }}">{{ __('messages.users_name') }}</div>
        <div class="md:col-span-2">{{ __('messages.users_mobile') }}</div>
        <div class="md:col-span-1">{{ __('messages.users_house') }}</div>
        <div class="{{ $showMainMemberColumn ? 'md:col-span-2' : 'md:col-span-3' }}">{{ __('messages.members_type') }}</div>
        @if ($showMainMemberColumn)
            <div class="md:col-span-2">{{ __('messages.members_main_member') }}</div>
        @endif
        <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
    </div>

    @forelse ($members as $member)
        <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
            <div class="mb-2 flex items-center gap-3 {{ $showMainMemberColumn ? 'md:col-span-3' : 'md:col-span-4' }} md:mb-0">
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
            <div class="mb-2 whitespace-nowrap text-sm text-[#0F141E] md:col-span-1 md:mb-0">{{ $member['house'] }}</div>
            <div class="mb-2 {{ $showMainMemberColumn ? 'md:col-span-2' : 'md:col-span-3' }} md:mb-0">
                <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                    {{ $member['membership_type'] }}
                </span>
            </div>
            @if ($showMainMemberColumn)
                <div class="mb-2 text-sm text-[#0F141E]/80 md:col-span-2 md:mb-0">{{ $member['main_member'] }}</div>
            @endif
            <div class="flex items-center justify-end gap-1 md:col-span-2">
                @can('members.update')
                    <form method="POST" action="{{ route('admin.members.open-edit') }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="member_id" value="{{ $member['id'] }}">
                        <x-common.icon-action
                            type="submit"
                            :title="__('messages.users_update')"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </x-common.icon-action>
                    </form>
                @endcan
                @can('members.delete')
                    <form method="POST" action="{{ route('admin.members.destroy') }}" class="inline-flex" onsubmit="return confirm(@js(__('messages.members_delete_confirm')))">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="member_id" value="{{ $member['id'] }}">
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
                @endcan
            </div>
        </div>
    @empty
        <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
            {{ $emptyMessage }}
        </div>
    @endforelse
</div>
