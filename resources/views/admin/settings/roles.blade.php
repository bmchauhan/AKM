<x-layouts.admin :pageTitle="__('messages.settings_roles')">
    <div
        x-data="rolesManager(@js($roles), @js(route('admin.settings.roles.sync')), @js([
            'systemDelete' => __('messages.roles_system_delete'),
            'inUseDelete' => __('messages.roles_in_use_delete_short'),
            'saveError' => __('messages.roles_save_error'),
            'networkError' => __('messages.roles_network_error'),
            'usersLabel' => __('messages.roles_users'),
        ]))"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">
                    {{ __('messages.settings') }}
                </p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.settings_roles') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.roles_subtitle') }}</p>
            </div>

            @can('settings.update')
                <div class="flex flex-wrap gap-3">
                    <x-common.button type="button" variant="secondary" @click="addRole()">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('messages.roles_add') }}
                    </x-common.button>

                    <x-common.button type="button" @click="save()" x-bind:disabled="saving">
                        <span x-show="!saving">{{ __('messages.roles_save') }}</span>
                        <span x-show="saving" x-cloak>{{ __('messages.roles_saving') }}</span>
                    </x-common.button>
                </div>
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-3">
                <div class="md:col-span-3">{{ __('messages.roles_name') }}</div>
                <div class="md:col-span-1">{{ __('messages.roles_short_form') }}</div>
                <div class="md:col-span-2">{{ __('messages.roles_slug') }}</div>
                <div class="md:col-span-3">{{ __('messages.roles_description') }}</div>
                <div class="md:col-span-1 text-center">{{ __('messages.roles_users_count') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.roles_actions') }}</div>
            </div>

            <template x-if="roles.length === 0">
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.roles_empty') }}
                </div>
            </template>

            <template x-for="(role, index) in roles" :key="role._key">
                <div
                    class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 md:grid md:grid-cols-12 md:items-start md:gap-3"
                    :class="rowHasError(index) ? 'bg-[#E5989B]/10' : ''"
                >
                    <div class="mb-3 md:col-span-3 md:mb-0">
                        <label class="mb-1 block text-xs font-semibold text-[#080D21] md:sr-only">
                            {{ __('messages.roles_name') }}
                        </label>
                        <input
                            type="text"
                            x-model="role.name"
                            @input="onNameInput(role)"
                            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                            placeholder="{{ __('messages.roles_name_placeholder') }}"
                        />
                        <p x-show="fieldError(index, 'name')" x-text="fieldError(index, 'name')" class="mt-1 text-xs text-[#AB1E23]" x-cloak></p>
                    </div>

                    <div class="mb-3 md:col-span-1 md:mb-0">
                        <label class="mb-1 block text-xs font-semibold text-[#080D21] md:sr-only">
                            {{ __('messages.roles_short_form') }}
                        </label>
                        <input
                            type="text"
                            x-model="role.short_form"
                            @input="onShortFormInput(role)"
                            maxlength="20"
                            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm font-semibold uppercase tracking-wide text-[#080D21] focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                            placeholder="{{ __('messages.roles_short_form_placeholder') }}"
                        />
                        <p x-show="fieldError(index, 'short_form')" x-text="fieldError(index, 'short_form')" class="mt-1 text-xs text-[#AB1E23]" x-cloak></p>
                    </div>

                    <div class="mb-3 md:col-span-2 md:mb-0">
                        <label class="mb-1 block text-xs font-semibold text-[#080D21] md:sr-only">
                            {{ __('messages.roles_slug') }}
                        </label>
                        <input
                            type="text"
                            x-model="role.slug"
                            @input="onSlugInput(role)"
                            :readonly="role.is_system"
                            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20 disabled:cursor-not-allowed disabled:bg-[#ECEAE1]/60"
                            placeholder="{{ __('messages.roles_slug_placeholder') }}"
                        />
                        <p x-show="fieldError(index, 'slug')" x-text="fieldError(index, 'slug')" class="mt-1 text-xs text-[#AB1E23]" x-cloak></p>
                        <p x-show="role.is_system" class="mt-1 text-xs text-[#0F141E]/50" x-cloak>{{ __('messages.roles_system_slug') }}</p>
                    </div>

                    <div class="mb-3 md:col-span-3 md:mb-0">
                        <label class="mb-1 block text-xs font-semibold text-[#080D21] md:sr-only">
                            {{ __('messages.roles_description') }}
                        </label>
                        <input
                            type="text"
                            x-model="role.description"
                            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                            placeholder="{{ __('messages.roles_description_placeholder') }}"
                        />
                    </div>

                    <div class="mb-3 flex items-center justify-center md:col-span-1 md:mb-0">
                        <label class="mb-1 block text-xs font-semibold text-[#080D21] md:sr-only">
                            {{ __('messages.roles_users_count') }}
                        </label>
                        <span
                            class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-[#E6EBF4] px-2.5 py-1 text-xs font-semibold text-[#080D21]"
                            x-text="role.users_count ?? 0"
                        ></span>
                    </div>

                    <div class="flex items-center justify-end gap-2 md:col-span-2">
                        <span
                            x-show="role.is_system"
                            class="rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]"
                            x-cloak
                        >
                            {{ __('messages.roles_system_badge') }}
                        </span>

                        <button
                            type="button"
                            @click="removeRole(index)"
                            :disabled="role.is_system || role.users_count > 0"
                            class="rounded-lg p-2 text-[#AB1E23] transition hover:bg-[#E5989B]/20 disabled:cursor-not-allowed disabled:opacity-30"
                            title="{{ __('messages.roles_delete') }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <p class="text-xs text-[#0F141E]/50">{{ __('messages.roles_hint') }}</p>
    </div>
</x-layouts.admin>
