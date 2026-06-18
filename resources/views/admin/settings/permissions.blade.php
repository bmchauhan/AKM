<x-layouts.admin :pageTitle="__('messages.settings_permissions')">
    <div
        x-data="permissionsManager(
            @js($modules),
            @js($module_groups),
            @js($roles),
            @js($permissions),
            @js(route('admin.settings.permissions.sync')),
            @js([
                'selectRole' => __('messages.permissions_select_role'),
                'create' => __('messages.permissions_create'),
                'read' => __('messages.permissions_read'),
                'update' => __('messages.permissions_update'),
                'delete' => __('messages.permissions_delete'),
                'all' => __('messages.permissions_all'),
                'saveError' => __('messages.permissions_save_error'),
                'networkError' => __('messages.permissions_network_error'),
            ])
        )"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.settings') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.settings_permissions') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.permissions_subtitle') }}</p>
            </div>

            @can('settings_permissions.update')
                <x-common.button type="button" class="shrink-0" @click="save()" x-bind:disabled="saving || !selectedRoleId">
                    <span x-show="!saving">{{ __('messages.permissions_save') }}</span>
                    <span x-show="saving" x-cloak>{{ __('messages.permissions_saving') }}</span>
                </x-common.button>
            @endcan
        </div>

        <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
            <label for="permissions-role" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.permissions_select_role') }}
            </label>
            <select
                id="permissions-role"
                x-model.number="selectedRoleId"
                class="w-full max-w-md rounded-lg border border-[#E6EBF4] bg-white px-3 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            >
                @foreach ($roles as $role)
                    <option value="{{ $role['id'] }}">{{ $role['name'] }} ({{ $role['short_form'] }})</option>
                @endforeach
            </select>
        </div>

        <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
            {{ __('messages.permissions_hint') }}
        </div>

        <div
            x-show="isSuperAdminRoleSelected"
            x-cloak
            class="rounded-xl border border-[#E6C280]/50 bg-[#E6C280]/15 px-4 py-3 text-sm text-[#080D21]"
        >
            {{ __('messages.permissions_super_admin_locked') }}
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#E6EBF4] bg-[#E6EBF4]/50 text-xs font-semibold uppercase tracking-wide text-[#080D21]">
                            <th class="min-w-[14rem] px-4 py-3 text-left">{{ __('messages.permissions_module') }}</th>
                            <th class="w-24 px-3 py-3 text-center" x-text="messages.create"></th>
                            <th class="w-24 px-3 py-3 text-center" x-text="messages.read"></th>
                            <th class="w-24 px-3 py-3 text-center" x-text="messages.update"></th>
                            <th class="w-24 px-3 py-3 text-center" x-text="messages.delete"></th>
                            <th class="w-24 px-3 py-3 text-center text-[#AB1E23]" x-text="messages.all"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($module_groups as $group)
                            @if ($group['parent']['is_group_header'])
                                <tr class="border-b border-[#E6EBF4] bg-[#ECEAE1]/40">
                                    <td class="px-4 py-3 align-middle" colspan="6">
                                        <div class="font-semibold text-[#080D21]">{{ $group['parent']['name'] }}</div>
                                        <div class="mt-0.5 font-mono text-xs text-[#0F141E]/50">{{ $group['parent']['slug'] }}</div>
                                        @if ($group['parent']['description'])
                                            <div class="mt-1.5 max-w-md text-xs leading-relaxed text-[#0F141E]/70">
                                                {{ $group['parent']['description'] }}
                                            </div>
                                        @endif
                                        <span class="mt-2 inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[#080D21]">
                                            {{ __('messages.modules_type_main') }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @foreach ($group['children'] as $child)
                                <tr
                                    @class([
                                        'border-b border-[#E6EBF4] last:border-b-0',
                                        'bg-[#ECEAE1]/20' => $group['parent']['is_group_header'],
                                        'hover:bg-[#ECEAE1]/20' => ! $group['parent']['is_group_header'],
                                    ])
                                >
                                    <td @class([
                                        'px-4 py-3 align-middle',
                                        'pl-10' => $group['parent']['is_group_header'],
                                    ])>
                                        <div class="flex items-start gap-2">
                                            @if ($group['parent']['is_group_header'])
                                                <span class="mt-0.5 text-[#0F141E]/30">└</span>
                                            @endif
                                            <div>
                                                <div @class([
                                                    'text-sm text-[#080D21]',
                                                    'font-medium' => $group['parent']['is_group_header'],
                                                    'font-semibold' => ! $group['parent']['is_group_header'],
                                                ])>{{ $child['name'] }}</div>
                                                <div class="mt-0.5 font-mono text-xs text-[#0F141E]/50">{{ $child['slug'] }}</div>
                                                @if ($child['description'] && ! $group['parent']['is_group_header'])
                                                    <div class="mt-1.5 max-w-md text-xs leading-relaxed text-[#0F141E]/70">
                                                        {{ $child['description'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <span @class([
                                            'mt-2 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[#080D21]',
                                            'bg-[#E6EBF4]' => $group['parent']['is_group_header'],
                                            'bg-[#E6C280]/30' => ! $group['parent']['is_group_header'],
                                        ])>
                                            {{ $group['parent']['is_group_header'] ? __('messages.modules_type_sub') : __('messages.modules_type_main') }}
                                        </span>
                                    </td>

                                    @foreach (['can_create', 'can_read', 'can_update', 'can_delete'] as $permissionFlag)
                                        <td class="px-3 py-3 text-center align-middle">
                                            <label
                                                class="inline-flex items-center justify-center rounded-lg p-2"
                                                x-bind:class="isSuperAdminRoleSelected ? 'cursor-default' : (isModuleDisabledFor({{ $child['id'] }}) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6EBF4]/60')"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                                    x-bind:checked="flagFor({{ $child['id'] }}, '{{ $permissionFlag }}')"
                                                    x-bind:disabled="isModuleDisabledFor({{ $child['id'] }})"
                                                    @change="toggleFlagFor({{ $child['id'] }}, '{{ $permissionFlag }}')"
                                                />
                                            </label>
                                        </td>
                                    @endforeach

                                    <td class="px-3 py-3 text-center align-middle">
                                        <label
                                            class="inline-flex items-center justify-center rounded-lg border border-[#E6C280]/40 bg-[#E6C280]/10 p-2"
                                            x-bind:class="isSuperAdminRoleSelected ? 'cursor-default' : (isModuleDisabledFor({{ $child['id'] }}) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6C280]/20')"
                                        >
                                            <input
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-[#E6C280] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                                x-bind:checked="isAllCheckedFor({{ $child['id'] }})"
                                                x-bind:disabled="isModuleDisabledFor({{ $child['id'] }})"
                                                @change="toggleAllFor({{ $child['id'] }})"
                                            />
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                                    {{ __('messages.modules_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
