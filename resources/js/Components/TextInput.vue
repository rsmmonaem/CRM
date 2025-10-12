<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 backdrop-blur-sm text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:bg-white/70 focus:bg-white shadow-sm hover:shadow-md focus:shadow-lg"
        v-model="model"
        ref="input"
    />
</template>
