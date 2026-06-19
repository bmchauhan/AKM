import Swal from 'sweetalert2';

const baseOptions = {
    icon: 'warning',
    showCancelButton: true,
    reverseButtons: true,
    focusCancel: true,
    customClass: {
        popup: 'akm-swal-popup',
        title: 'akm-swal-title',
        htmlContainer: 'akm-swal-text',
        actions: 'akm-swal-actions',
        confirmButton: 'akm-swal-btn akm-swal-btn-danger',
        cancelButton: 'akm-swal-btn akm-swal-btn-cancel',
    },
    buttonsStyling: false,
};

export async function confirmDelete({
    title = 'Are you sure?',
    message = '',
    confirmText = 'Yes, delete',
    cancelText = 'Cancel',
} = {}) {
    const result = await Swal.fire({
        ...baseOptions,
        title,
        text: message,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
    });

    return result.isConfirmed;
}

window.confirmDelete = confirmDelete;

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-confirm-delete]');

    if (! form || form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const confirmed = await confirmDelete({
        title: form.dataset.confirmTitle || undefined,
        message: form.dataset.confirmMessage || undefined,
        confirmText: form.dataset.confirmYes || undefined,
        cancelText: form.dataset.confirmCancel || undefined,
    });

    if (confirmed) {
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    }
}, true);
