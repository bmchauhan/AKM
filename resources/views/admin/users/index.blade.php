<x-layouts.admin :pageTitle="__('messages.users_all')">
    <div class="space-y-6">
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

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-3">
                <div class="md:col-span-3">{{ __('messages.users_name') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_mobile') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_house') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_role') }}</div>
                <div class="md:col-span-1">{{ __('messages.users_gender') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($users as $user)
                <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-3">
                    <div class="mb-2 flex items-center gap-3 md:col-span-3 md:mb-0">
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
                    <div class="mb-2 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $user['house'] }}</div>
                    <div class="mb-2 md:col-span-2 md:mb-0">
                        <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ $user['role'] }}
                        </span>
                    </div>
                    <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-1 md:mb-0">{{ $user['gender'] }}</div>
                    <div class="flex justify-end gap-2 md:col-span-2">
                        @can('users.update')
                            @if (! $user['is_super_admin'] || auth()->user()->can('super-admin'))
                                <a
                                    href="{{ route('admin.users.edit', $user['id']) }}"
                                    class="rounded-lg border border-[#E6EBF4] px-3 py-1.5 text-xs font-semibold text-[#080D21] transition hover:border-[#E6C280] hover:bg-[#ECEAE1]"
                                >
                                    {{ __('messages.users_update') }}
                                </a>
                            @endif
                        @endcan
                        @can('users.delete')
                            @unless ($user['is_super_admin'])
                            <form method="POST" action="{{ route('admin.users.destroy', $user['id']) }}" onsubmit="return confirm(@js(__('messages.users_delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="rounded-lg border border-[#E5989B]/40 px-3 py-1.5 text-xs font-semibold text-[#AB1E23] transition hover:bg-[#E5989B]/15"
                                >
                                    {{ __('messages.users_remove') }}
                                </button>
                            </form>
                            @endunless
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.users_empty') }}
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
