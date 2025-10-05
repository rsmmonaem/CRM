<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    message: {
        type: String,
        required: true
    },
    duration: {
        type: Number,
        default: 5000
    },
    position: {
        type: String,
        default: 'top-right',
        validator: (value) => ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'].includes(value)
    }
});

const emit = defineEmits(['close']);

const isVisible = ref(false);
const isLeaving = ref(false);
let timeoutId = null;

const showToast = () => {
    isVisible.value = true;
    
    if (props.duration > 0) {
        timeoutId = setTimeout(() => {
            hideToast();
        }, props.duration);
    }
};

const hideToast = () => {
    isLeaving.value = true;
    setTimeout(() => {
        emit('close');
    }, 300);
};

const handleClick = () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }
    hideToast();
};

onMounted(() => {
    showToast();
});

onUnmounted(() => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }
});

const getIcon = () => {
    switch (props.type) {
        case 'success':
            return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'error':
            return 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'warning':
            return 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z';
        case 'info':
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        default:
            return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    }
};

const getColors = () => {
    switch (props.type) {
        case 'success':
            return {
                bg: 'bg-green-50',
                border: 'border-green-200',
                text: 'text-green-800',
                icon: 'text-green-400',
                iconBg: 'bg-green-100'
            };
        case 'error':
            return {
                bg: 'bg-red-50',
                border: 'border-red-200',
                text: 'text-red-800',
                icon: 'text-red-400',
                iconBg: 'bg-red-100'
            };
        case 'warning':
            return {
                bg: 'bg-yellow-50',
                border: 'border-yellow-200',
                text: 'text-yellow-800',
                icon: 'text-yellow-400',
                iconBg: 'bg-yellow-100'
            };
        case 'info':
            return {
                bg: 'bg-blue-50',
                border: 'border-blue-200',
                text: 'text-blue-800',
                icon: 'text-blue-400',
                iconBg: 'bg-blue-100'
            };
        default:
            return {
                bg: 'bg-green-50',
                border: 'border-green-200',
                text: 'text-green-800',
                icon: 'text-green-400',
                iconBg: 'bg-green-100'
            };
    }
};

const getPositionClasses = () => {
    switch (props.position) {
        case 'top-right':
            return 'top-4 right-4';
        case 'top-left':
            return 'top-4 left-4';
        case 'bottom-right':
            return 'bottom-4 right-4';
        case 'bottom-left':
            return 'bottom-4 left-4';
        case 'top-center':
            return 'top-4 left-1/2 transform -translate-x-1/2';
        case 'bottom-center':
            return 'bottom-4 left-1/2 transform -translate-x-1/2';
        default:
            return 'top-4 right-4';
    }
};

const colors = getColors();
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isVisible && !isLeaving"
            :class="[
                'fixed z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
                colors.bg,
                colors.border,
                getPositionClasses()
            ]"
            @click="handleClick"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div :class="['rounded-full p-2', colors.iconBg]">
                            <svg class="h-6 w-6" :class="colors.icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon()" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p :class="['text-sm font-medium', colors.text]">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="hideToast"
                            :class="['inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2', colors.text]"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
