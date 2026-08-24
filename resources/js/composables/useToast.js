import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 1;

function push(message, type = 'success', duration = 3000) {
    const id = nextId++;
    toasts.push({ id, message, type });
    setTimeout(() => {
        const idx = toasts.findIndex((t) => t.id === id);
        if (idx !== -1) toasts.splice(idx, 1);
    }, duration);
}

export function useToast() {
    return {
        toasts,
        success: (msg) => push(msg, 'success'),
        error: (msg) => push(msg, 'error'),
        info: (msg) => push(msg, 'info'),
    };
}
