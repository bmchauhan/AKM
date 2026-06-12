document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', (config = {}) => ({
        open: false,
        search: '',
        selected: config.selected !== null && config.selected !== undefined && config.selected !== ''
            ? String(config.selected)
            : '',
        options: config.options ?? [],
        placeholder: config.placeholder ?? 'Select an option',
        searchPlaceholder: config.searchPlaceholder ?? 'Type to search...',
        noResultsText: config.noResultsText ?? 'No matches found',
        submitOnChange: Boolean(config.submitOnChange),
        fieldName: config.name ?? '',

        get filteredOptions() {
            const term = this.search.trim().toLowerCase();

            if (term === '') {
                return this.options;
            }

            return this.options.filter((option) => option.label.toLowerCase().includes(term));
        },

        get selectedLabel() {
            const match = this.options.find((option) => String(option.value) === this.selected);

            return match?.label ?? '';
        },

        toggle() {
            this.open = ! this.open;

            if (this.open) {
                this.search = '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            }
        },

        close() {
            this.open = false;
            this.search = '';
        },

        selectOption(option) {
            this.selected = String(option.value);
            this.syncHiddenInput();
            this.close();

            this.$dispatch('searchable-select-changed', {
                value: this.selected,
                label: option.label,
            });

            if (this.submitOnChange && this.selected !== '') {
                this.$nextTick(() => {
                    this.syncHiddenInput();
                    this.$el.closest('form')?.requestSubmit();
                });
            }
        },

        syncHiddenInput() {
            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = this.selected;
            }
        },

        init() {
            this.$nextTick(() => this.syncHiddenInput());
        },

        clearSelection() {
            this.selected = '';
            this.syncHiddenInput();
            this.close();

            this.$dispatch('searchable-select-changed', {
                value: '',
                label: '',
            });
        },
    }));
});
