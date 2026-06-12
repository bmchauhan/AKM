document.addEventListener('alpine:init', () => {
    Alpine.data('usersList', (config = {}) => ({
        open: false,
        modalLoading: false,
        title: '',
        modalMainMember: null,
        householdMembers: [],
        emptyMessage: config.emptyMessage ?? 'No household members found.',
        loadingMessage: config.loadingMessage ?? 'Loading...',

        async openHousehold(userId) {
            this.open = true;
            this.modalLoading = true;
            this.title = '';
            this.modalMainMember = null;
            this.householdMembers = [];

            try {
                const response = await fetch(config.householdUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ user_id: userId }),
                });

                if (! response.ok) {
                    throw new Error('Failed to load household members.');
                }

                const data = await response.json();
                this.modalMainMember = data.main_member ?? null;
                this.title = this.modalMainMember?.name ?? '';
                this.householdMembers = data.members ?? [];
            } catch (error) {
                this.householdMembers = [];
            } finally {
                this.modalLoading = false;
            }
        },

        closeModal() {
            this.open = false;
        },
    }));
});
