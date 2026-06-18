const CRUD_FLAGS = ['can_create', 'can_read', 'can_update', 'can_delete'];

const emptyFlags = () => ({
    can_create: false,
    can_read: false,
    can_update: false,
    can_delete: false,
});

const fullFlags = () => ({
    can_create: true,
    can_read: true,
    can_update: true,
    can_delete: true,
});

document.addEventListener('alpine:init', () => {
    Alpine.data('permissionsManager', (modules = [], moduleGroups = [], roles = [], initialPermissions = {}, syncUrl = '', messages = {}) => ({
        modules,
        moduleGroups,
        roles,
        messages,
        permissions: {},
        selectedRoleId: null,
        saving: false,

        init() {
            this.roles.forEach((role) => {
                this.permissions[role.id] = {};

                this.modules.forEach((module) => {
                    const rolePerms = initialPermissions[role.id] ?? initialPermissions[String(role.id)] ?? {};
                    const modulePerms = rolePerms[module.id] ?? rolePerms[String(module.id)] ?? {};

                    if (role.is_super_admin) {
                        this.permissions[role.id][module.id] = fullFlags();
                    } else {
                        this.permissions[role.id][module.id] = {
                            can_create: Boolean(modulePerms.can_create),
                            can_read: Boolean(modulePerms.can_read),
                            can_update: Boolean(modulePerms.can_update),
                            can_delete: Boolean(modulePerms.can_delete),
                        };
                    }
                });
            });

            this.selectedRoleId = this.roles.find((role) => role.is_super_admin)?.id
                ?? this.roles[0]?.id
                ?? null;
        },

        get isSuperAdminRoleSelected() {
            return Boolean(this.selectedRole?.is_super_admin);
        },

        get selectedRole() {
            return this.roles.find((role) => role.id === this.selectedRoleId) ?? null;
        },

        moduleFlags(module) {
            if (! this.selectedRoleId) {
                return emptyFlags();
            }

            return this.permissions[this.selectedRoleId]?.[module.id] ?? emptyFlags();
        },

        flag(key, module) {
            return Boolean(this.moduleFlags(module)[key]);
        },

        isModuleDisabled(module) {
            const role = this.selectedRole;

            if (! role) {
                return true;
            }

            if (role.is_super_admin) {
                return true;
            }

            return module.settings_only;
        },

        isAllChecked(module) {
            const flags = this.moduleFlags(module);

            return CRUD_FLAGS.every((key) => flags[key]);
        },

        moduleById(moduleId) {
            return this.modules.find((module) => module.id === moduleId) ?? { id: moduleId };
        },

        flagFor(moduleId, key) {
            return this.flag(key, this.moduleById(moduleId));
        },

        isAllCheckedFor(moduleId) {
            return this.isAllChecked(this.moduleById(moduleId));
        },

        isModuleDisabledFor(moduleId) {
            return this.isModuleDisabled(this.moduleById(moduleId));
        },

        toggleFlagFor(moduleId, key) {
            this.toggleFlag(this.moduleById(moduleId), key);
        },

        toggleAllFor(moduleId) {
            this.toggleAll(this.moduleById(moduleId));
        },

        setModuleFlags(module, flags) {
            if (! this.selectedRoleId || this.isModuleDisabled(module)) {
                return;
            }

            this.permissions[this.selectedRoleId][module.id] = { ...flags };
        },

        toggleFlag(module, key) {
            if (this.isModuleDisabled(module)) {
                return;
            }

            const flags = { ...this.moduleFlags(module) };
            flags[key] = ! flags[key];
            this.setModuleFlags(module, flags);
        },

        toggleAll(module) {
            if (this.isModuleDisabled(module)) {
                return;
            }

            this.setModuleFlags(module, this.isAllChecked(module) ? emptyFlags() : fullFlags());
        },

        payloadForSelectedRole() {
            return this.modules.map((module) => ({
                module_id: module.id,
                ...this.moduleFlags(module),
            }));
        },

        applyServerPermissions(serverPermissions) {
            this.roles.forEach((role) => {
                const rolePerms = serverPermissions[role.id] ?? serverPermissions[String(role.id)] ?? {};

                this.modules.forEach((module) => {
                    const modulePerms = rolePerms[module.id] ?? rolePerms[String(module.id)] ?? {};

                    if (role.is_super_admin) {
                        this.permissions[role.id][module.id] = fullFlags();
                    } else {
                        this.permissions[role.id][module.id] = {
                            can_create: Boolean(modulePerms.can_create),
                            can_read: Boolean(modulePerms.can_read),
                            can_update: Boolean(modulePerms.can_update),
                            can_delete: Boolean(modulePerms.can_delete),
                        };
                    }
                });
            });
        },

        async save() {
            if (! this.selectedRoleId) {
                return;
            }

            this.saving = true;

            try {
                const response = await fetch(syncUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        role_id: this.selectedRoleId,
                        permissions: this.payloadForSelectedRole(),
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    window.toast?.('error', data.message ?? messages.saveError ?? 'Could not save permissions.');
                    return;
                }

                if (data.permissions) {
                    this.applyServerPermissions(data.permissions);
                }

                if (data.module_groups) {
                    this.moduleGroups = data.module_groups;
                }

                window.toast?.('success', data.message ?? 'Permissions saved.');
            } catch {
                window.toast?.('error', messages.networkError ?? 'Could not save permissions. Please try again.');
            } finally {
                this.saving = false;
            }
        },
    }));
});
