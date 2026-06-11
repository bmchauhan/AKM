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
            _key: role.id ? `role-${role.id}` : `new-${crypto.randomUUID()}`,
            _slugTouched: Boolean(role.id),
            _shortTouched: Boolean(role.id),
        })),
        deletedIds: [],
        saving: false,
        errors: {},

        addRole() {
            this.roles.push({
                id: null,
                name: '',
                short_form: '',
                slug: '',
                description: '',
                is_system: false,
                users_count: 0,
                _key: `new-${crypto.randomUUID()}`,
                _slugTouched: false,
                _shortTouched: false,
            });
        },

        onNameInput(role) {
            if (! role._slugTouched && ! role.is_system) {
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

        removeRole(index) {
            const role = this.roles[index];

            if (role.is_system) {
                window.toast?.('warning', messages.systemDelete ?? 'This role cannot be deleted.');
                return;
            }

            if (role.users_count > 0) {
                window.toast?.('warning', messages.inUseDelete ?? 'This role is assigned to users.');
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
                        roles: this.roles.map(({ id, name, short_form, slug, description }) => ({
                            id,
                            name,
                            short_form,
                            slug,
                            description,
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

                this.roles = data.roles.map((role) => ({
                    ...role,
                    description: role.description ?? '',
                    short_form: role.short_form ?? '',
                    _key: `role-${role.id}`,
                    _slugTouched: true,
                    _shortTouched: true,
                }));
                this.deletedIds = [];

                window.toast?.('success', data.message ?? 'Roles saved.');
            } catch {
                window.toast?.('error', messages.networkError ?? 'Could not save roles. Please try again.');
            } finally {
                this.saving = false;
            }
        },
    }));
});
