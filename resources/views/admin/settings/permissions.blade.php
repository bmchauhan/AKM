<x-layouts.admin :pageTitle="__('messages.settings_permissions')">
    <div
        x-data="permissionsManager(
            @js($modules),
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

            @can('settings.update')
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
                <template x-for="role in roles" :key="role.id">
                    <option :value="role.id" x-text="`${role.name} (${role.short_form})`"></option>
                </template>
            </select>
        </div>

        <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
            {{ __('messages.permissions_hint') }}
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
                        <template x-for="module in modules" :key="module.id">
                            <tr
                                class="border-b border-[#E6EBF4] last:border-b-0"
                                :class="isModuleDisabled(module) ? 'bg-[#ECEAE1]/30' : 'hover:bg-[#ECEAE1]/20'"
                            >
                                <td class="px-4 py-4 align-middle">
                                    <div class="font-semibold text-[#080D21]" x-text="module.name"></div>
                                    <div class="mt-0.5 font-mono text-xs text-[#0F141E]/50" x-text="module.slug"></div>
                                    <div
                                        class="mt-1.5 max-w-md text-xs leading-relaxed text-[#0F141E]/70"
                                        x-show="module.description"
                                        x-text="module.description"
                                    ></div>
                                </td>

                                <td class="px-3 py-4 text-center align-middle">
                                    <label
                                        class="inline-flex items-center justify-center rounded-lg p-2"
                                        :class="isModuleDisabled(module) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6EBF4]/60'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                            :checked="flag('can_create', module)"
                                            :disabled="isModuleDisabled(module)"
                                            @change="toggleFlag(module, 'can_create')"
                                        />
                                    </label>
                                </td>

                                <td class="px-3 py-4 text-center align-middle">
                                    <label
                                        class="inline-flex items-center justify-center rounded-lg p-2"
                                        :class="isModuleDisabled(module) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6EBF4]/60'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                            :checked="flag('can_read', module)"
                                            :disabled="isModuleDisabled(module)"
                                            @change="toggleFlag(module, 'can_read')"
                                        />
                                    </label>
                                </td>

                                <td class="px-3 py-4 text-center align-middle">
                                    <label
                                        class="inline-flex items-center justify-center rounded-lg p-2"
                                        :class="isModuleDisabled(module) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6EBF4]/60'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                            :checked="flag('can_update', module)"
                                            :disabled="isModuleDisabled(module)"
                                            @change="toggleFlag(module, 'can_update')"
                                        />
                                    </label>
                                </td>

                                <td class="px-3 py-4 text-center align-middle">
                                    <label
                                        class="inline-flex items-center justify-center rounded-lg p-2"
                                        :class="isModuleDisabled(module) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6EBF4]/60'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                            :checked="flag('can_delete', module)"
                                            :disabled="isModuleDisabled(module)"
                                            @change="toggleFlag(module, 'can_delete')"
                                        />
                                    </label>
                                </td>

                                <td class="px-3 py-4 text-center align-middle">
                                    <label
                                        class="inline-flex items-center justify-center rounded-lg border border-[#E6C280]/40 bg-[#E6C280]/10 p-2"
                                        :class="isModuleDisabled(module) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-[#E6C280]/20'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#E6C280] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                                            :checked="isAllChecked(module)"
                                            :disabled="isModuleDisabled(module)"
                                            @change="toggleAll(module)"
                                        />
                                    </label>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
