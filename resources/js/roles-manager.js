const slugify = (value) => value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s_-]/g, '')
    .replace(/[\s-]+/g, '_')
    .replace(/_+/g, '_')
    .replace(/^_|_$/g, '');

const toShortForm = (value) => value
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .map((word) => word.charAt(0))
    .join('')
    .toUpperCase()
    .replace(/[^A-Z0-9]/g, '')
    .slice(0, 20);

document.addEventListener('alpine:init', () => {
    Alpine.data('rolesManager', (initialRoles = [], syncUrl = '', messages = {}) => ({
        messages,
        roles: initialRoles.map((role) => ({
            ...role,
            description: role.description ?? '',
            short_form: role.short_form ?? '',
            role_type: role.role_type ?? 'committee',
            role_type_label: role.role_type_label ?? messages.typeCommittee ?? 'Committee',
            is_leadership: Boolean(role.is_leadership),
            is_super_admin: Boolean(role.is_super_admin),
            slug_locked: Boolean(role.slug_locked),
            can_assign_user: role.can_assign_user ?? (role.role_type === 'committee' || role.is_super_admin),
            _key: role.id ? `role-${role.id}` : `new-${crypto.randomUUID()}`,
            _slugTouched: Boolean(role.id),
            _shortTouched: Boolean(role.id),
        })),
        deletedIds: [],
        saving: false,
        errors: {},
        permissionsUrl: messages.permissionsUrl ?? '',

        addRole() {
            this.roles.push({
                id: null,
                name: '',
                short_form: '',
                slug: '',
                description: '',
                is_system: false,
                role_type: 'committee',
                role_type_label: messages.typeCommittee ?? 'Committee',
                is_leadership: false,
                is_super_admin: false,
                slug_locked: false,
                can_assign_user: true,
                users_count: 0,
                _key: `new-${crypto.randomUUID()}`,
                _slugTouched: false,
                _shortTouched: false,
            });
        },

        isCommitteeRole(role) {
            return role.role_type === 'committee' && ! role.is_super_admin;
        },

        onNameInput(role) {
            if (! role._slugTouched && ! role.is_system && ! role.slug_locked) {
                role.slug = slugify(role.name);
            }

            if (! role._shortTouched) {
                role.short_form = toShortForm(role.name);
            }
        },

        onShortFormInput(role) {
            role._shortTouched = true;
            role.short_form = role.short_form.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 20);
        },

        onSlugInput(role) {
            role._slugTouched = true;
            role.slug = slugify(role.slug);
        },

        async removeRole(index) {
            const role = this.roles[index];

            if (role.is_system) {
                window.toast?.('warning', messages.systemDelete ?? 'This role cannot be deleted.');
                return;
            }

            if (role.users_count > 0) {
                window.toast?.('warning', messages.inUseDelete ?? 'This role is assigned to users.');
                return;
            }

            const confirmed = await window.confirmDelete?.({
                title: messages.deleteConfirmTitle ?? 'Are you sure?',
                message: messages.deleteConfirm ?? 'Are you sure you want to remove this role?',
                confirmText: messages.deleteConfirmYes ?? 'Yes, delete',
                cancelText: messages.deleteConfirmCancel ?? 'Cancel',
            });

            if (! confirmed) {
                return;
            }

            if (role.id) {
                this.deletedIds.push(role.id);
            }

            this.roles.splice(index, 1);
        },

        fieldError(index, field) {
            return this.errors[`roles.${index}.${field}`]?.[0] ?? '';
        },

        rowHasError(index) {
            return Object.keys(this.errors).some((key) => key.startsWith(`roles.${index}.`));
        },

        mapRolesFromResponse(roles) {
            return roles.map((role) => ({
                ...role,
                description: role.description ?? '',
                short_form: role.short_form ?? '',
                role_type: role.role_type ?? 'committee',
                role_type_label: role.role_type_label ?? messages.typeCommittee ?? 'Committee',
                is_leadership: Boolean(role.is_leadership),
                is_super_admin: Boolean(role.is_super_admin),
                slug_locked: Boolean(role.slug_locked),
                can_assign_user: Boolean(role.can_assign_user),
                _key: `role-${role.id}`,
                _slugTouched: true,
                _shortTouched: true,
            }));
        },

        refreshAssignableSlugs() {
            this.assignableSlugs = this.roles
                .filter((role) => role.can_assign_user)
                .map((role) => role.slug);
        },

        async save() {
            this.saving = true;
            this.errors = {};

            try {
                const response = await fetch(syncUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        roles: this.roles.map(({ id, name, short_form, slug, description, is_leadership }) => ({
                            id,
                            name,
                            short_form,
                            slug,
                            description,
                            is_leadership: Boolean(is_leadership),
                        })),
                        deleted_ids: this.deletedIds,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    if (data.errors) {
                        this.errors = data.errors;
                    }

                    window.toast?.('error', data.message ?? messages.saveError ?? 'Could not save roles.');
                    return;
                }

                this.roles = this.mapRolesFromResponse(data.roles ?? []);
                this.deletedIds = [];
                this.refreshAssignableSlugs();

                window.toast?.('success', data.message ?? 'Roles saved.');

                if (data.created_hint) {
                    window.toast?.('info', data.created_hint);

                    if (data.permissions_url || this.permissionsUrl) {
                        setTimeout(() => {
                            window.location.href = data.permissions_url || this.permissionsUrl;
                        }, 1200);
                    }
                }
            } catch {
                window.toast?.('error', messages.networkError ?? 'Could not save roles. Please try again.');
            } finally {
                this.saving = false;
            }
        },

        assignUserOpen: false,
        assignRoleSlug: '',
        assignRoleName: '',
        assignUserId: '',
        assignableSlugs: messages.assignableSlugs ?? [],
        usersForAssign: messages.usersForAssign ?? [],
        assignUrl: messages.assignUrl ?? '',

        get open() {
            return this.assignUserOpen;
        },

        set open(value) {
            this.assignUserOpen = value;
        },

        get title() {
            return this.messages.assignUserTitle ?? 'Assign user';
        },

        canAssignUser(role) {
            return Boolean(role.can_assign_user) || this.assignableSlugs.includes(role.slug);
        },

        openAssignUser(role) {
            this.assignRoleSlug = role.slug;
            this.assignRoleName = role.name;
            this.assignUserId = '';
            this.assignUserOpen = true;
        },
    }));
});
