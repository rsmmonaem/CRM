<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    service: Object,
});

const form = useForm({
    name: props.service.name,
});

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>

<template>
    <Head title="Edit Service" />

    <ModernLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Service: {{ service.name }}
            </h1>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Service Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Enter service name..."
                            required
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a
                            :href="route('services.show', service.id)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg disabled:opacity-50"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Service' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernLayout>
</template>
