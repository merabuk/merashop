import { defineStore } from 'pinia';
import { ref } from 'vue';

export enum ToastType {
    Success = 'success',
    Error = 'error',
    Warning = 'warning',
    Info = 'info',
}

interface Toast {
    id: number;
    message: string;
    type: ToastType;
}

const STORAGE_KEY = 'merashop_pending_toasts';

export const useToastStore = defineStore('shared-toast', () => {
    const toasts = ref<Toast[]>([]);
    let counter = 0;

    function init() {
        const stored = sessionStorage.getItem(STORAGE_KEY);
        if (stored) {
            try {
                const pendingToasts = JSON.parse(stored);
                pendingToasts.forEach((t: Toast) => add(t.message, t.type));
                sessionStorage.removeItem(STORAGE_KEY);
            } catch {
                sessionStorage.removeItem(STORAGE_KEY);
            }
        }
    }

    function add(message: string, type: ToastType = ToastType.Info, duration = 5000) {
        const id = ++counter;
        toasts.value.push({ id, message, type });

        setTimeout(() => {
            remove(id);
        }, duration);
    }

    function persist(message: string, type: ToastType = ToastType.Success) {
        const stored = sessionStorage.getItem(STORAGE_KEY);
        const pending = stored ? JSON.parse(stored) : [];
        pending.push({ message, type });
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(pending));
    }

    function remove(id: number) {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }

    return {
        toasts,
        init,
        persist,
        success: (msg: string) => add(msg, ToastType.Success),
        error: (msg: string) => add(msg, ToastType.Error),
        warning: (msg: string) => add(msg, ToastType.Warning),
        info: (msg: string) => add(msg, ToastType.Info),
        remove
    };
});
