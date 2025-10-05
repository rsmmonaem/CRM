<script setup>
import { ref, provide } from 'vue';
import Toast from './Toast.vue';

const toasts = ref([]);

const addToast = (toast) => {
    const id = Date.now() + Math.random();
    toasts.value.push({
        id,
        ...toast
    });
};

const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

// Provide toast functions to child components
provide('toast', {
    success: (message, options = {}) => addToast({ type: 'success', message, ...options }),
    error: (message, options = {}) => addToast({ type: 'error', message, ...options }),
    warning: (message, options = {}) => addToast({ type: 'warning', message, ...options }),
    info: (message, options = {}) => addToast({ type: 'info', message, ...options }),
});

// Listen for flash messages from Laravel
const handleFlashMessages = () => {
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');
    const flashWarning = document.querySelector('[data-flash-warning]');
    const flashInfo = document.querySelector('[data-flash-info]');

    if (flashSuccess) {
        addToast({ type: 'success', message: flashSuccess.textContent });
        flashSuccess.remove();
    }
    if (flashError) {
        addToast({ type: 'error', message: flashError.textContent });
        flashError.remove();
    }
    if (flashWarning) {
        addToast({ type: 'warning', message: flashWarning.textContent });
        flashWarning.remove();
    }
    if (flashInfo) {
        addToast({ type: 'info', message: flashInfo.textContent });
        flashInfo.remove();
    }
};

// Initialize flash message handling
handleFlashMessages();
</script>

<template>
    <div class="fixed inset-0 z-50 pointer-events-none">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :type="toast.type"
            :message="toast.message"
            :duration="toast.duration"
            :position="toast.position"
            @close="removeToast(toast.id)"
        />
    </div>
</template>
