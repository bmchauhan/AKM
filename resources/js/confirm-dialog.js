import Swal from 'sweetalert2';

const swalClasses = {
    popup: 'akm-swal-popup',
    title: 'akm-swal-title',
    htmlContainer: 'akm-swal-text',
    actions: 'akm-swal-actions',
    confirmButton: 'akm-swal-btn akm-swal-btn-danger',
    cancelButton: 'akm-swal-btn akm-swal-btn-cancel',
};

const confirmBaseOptions = {
    icon: 'warning',
    showCancelButton: true,
    reverseButtons: true,
    focusCancel: true,
    customClass: swalClasses,
    buttonsStyling: false,
};

export async function confirmDelete({
    title = 'Are you sure?',
    message = '',
    confirmText = 'Yes, delete',
    cancelText = 'Cancel',
} = {}) {
    const result = await Swal.fire({
        ...confirmBaseOptions,
        title,
        text: message,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
    });

    return result.isConfirmed;
}

export function showAlertDialog({
    title = '',
    message = '',
    icon = 'warning',
    confirmText = 'OK',
    code = null,
    actions = [],
} = {}) {
    const confirmButtonClass = icon === 'success'
        ? 'akm-swal-btn akm-swal-btn-primary'
        : 'akm-swal-btn akm-swal-btn-danger';

    let html = null;

    if (Array.isArray(actions) && actions.length > 0) {
        const actionButtons = actions
            .map((action) => {
                const label = action.label ?? '';
                const url = action.url ?? '#';
                const type = action.type ?? 'link';

                if (type === 'link' && url) {
                    return `<a href="${url}" class="akm-swal-action-link">${label}</a>`;
                }

                return `<button type="button" class="akm-swal-action-btn" data-action="${type}">${label}</button>`;
            })
            .join('');

        html = `<p class="akm-swal-text mb-0">${message}</p><div class="akm-swal-extra-actions">${actionButtons}</div>`;
    }

    return Swal.fire({
        icon,
        title: title || undefined,
        text: html ? undefined : message,
        html: html || undefined,
        showCancelButton: false,
        confirmButtonText: confirmText,
        focusConfirm: true,
        customClass: {
            ...swalClasses,
            confirmButton: confirmButtonClass,
        },
        buttonsStyling: false,
        didOpen: () => {
            if (code) {
                Swal.getPopup()?.setAttribute('data-swal-code', code);
            }
        },
    });
}

window.confirmDelete = confirmDelete;
window.showAlertDialog = showAlertDialog;

function loadFlashSwalDialogs() {
    const bridge = document.getElementById('flash-swal-json');

    if (! bridge?.textContent) {
        return;
    }

    try {
        const dialog = JSON.parse(bridge.textContent.trim());

        if (dialog?.message || dialog?.title) {
            showAlertDialog({
                title: dialog.title,
                message: dialog.message,
                icon: dialog.icon,
                confirmText: dialog.confirm_text,
                code: dialog.code,
                actions: dialog.actions,
            });
        }
    } catch {
        // ignore invalid flash payload
    }

    bridge.remove();
}

document.addEventListener('DOMContentLoaded', loadFlashSwalDialogs);

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
