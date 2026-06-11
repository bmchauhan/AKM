import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

const TOAST_DURATION_MS = 4000;

const icon = (paths, color = '#AB1E23') => `
    <svg class="akm-toast__icon" viewBox="0 0 24 24" fill="none" stroke="${color}" aria-hidden="true">
        ${paths}
    </svg>
`;

const toastIcons = {
    success: icon(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        '#3A8F65',
    ),
    error: icon(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        '#AB1E23',
    ),
    warning: icon(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        '#8A6B2A',
    ),
    info: icon(
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        '#080D21',
    ),
};

const notyf = new Notyf({
    duration: TOAST_DURATION_MS,
    dismissible: true,
    ripple: false,
    position: { x: 'right', y: 'top' },
    types: [
        {
            type: 'success',
            background: '#F4FBF6',
            className: 'akm-toast akm-toast--success',
            icon: toastIcons.success,
        },
        {
            type: 'error',
            background: '#FFFFFF',
            className: 'akm-toast akm-toast--error',
            icon: toastIcons.error,
        },
        {
            type: 'warning',
            background: '#FFFBF2',
            className: 'akm-toast akm-toast--warning',
            icon: toastIcons.warning,
        },
        {
            type: 'info',
            background: '#FFFFFF',
            className: 'akm-toast akm-toast--info',
            icon: toastIcons.info,
        },
    ],
});

function showToast(type, message) {
    if (! message) {
        return;
    }

    const toastType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';

    notyf.open({ type: toastType, message });
}

function loadFlashToasts() {
    const bridge = document.getElementById('flash-toasts-json');

    if (! bridge?.textContent) {
        return;
    }

    try {
        const payload = JSON.parse(bridge.textContent.trim());

        if (Array.isArray(payload)) {
            payload.forEach((toast) => {
                if (toast?.message) {
                    showToast(toast.type ?? 'info', toast.message);
                }
            });
        }
    } catch {
        // ignore invalid flash payload
    }

    bridge.remove();
}

document.addEventListener('DOMContentLoaded', loadFlashToasts);

window.toast = (type, message) => {
    showToast(type, message);
};
