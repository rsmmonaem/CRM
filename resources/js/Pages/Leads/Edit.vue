<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    lead: Object,
    services: Array,
    statuses: Array,
    users: Array,
});

const form = useForm({
    name: props.lead.name,
    company_name: props.lead.company_name,
    phone: props.lead.phone,
    email: props.lead.email,
    service_id: props.lead.service_id,
    status_id: props.lead.status_id,
    assigned_user_id: props.lead.assigned_user_id,
});

const submit = () => {
    form.put(route('leads.update', props.lead.id));
};
</script>

<template>
    <Head title="Edit Lead" />

    <ModernLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Lead: {{ lead.name }}
            </h1>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name *
                            </label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Company Name
                            </label>
                            <input
                                type="text"
                                id="company_name"
                                v-model="form.company_name"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            />
                            <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.company_name }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                v-model="form.phone"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter phone number"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.phone }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter email address"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Contact validation error -->
                        <div v-if="form.errors.contact" class="md:col-span-2 mt-2 p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800 dark:text-red-200">{{ form.errors.contact }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Duplicate error -->
                        <div v-if="form.errors.duplicate" class="md:col-span-2 mt-2 p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800 dark:text-red-200">{{ form.errors.duplicate }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Service -->
                        <div>
                            <label for="service_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Service *
                            </label>
                            <select
                                id="service_id"
                                v-model="form.service_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            >
                                <option value="">Select a service</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.service_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.service_id }}
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status *
                            </label>
                            <select
                                id="status_id"
                                v-model="form.status_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            >
                                <option value="">Select a status</option>
                                <option v-for="status in statuses" :key="status.id" :value="status.id">
                                    {{ status.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.status_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.status_id }}
                            </div>
                        </div>

                        <!-- Assigned User -->
                        <div>
                            <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Assign To *
                            </label>
                            <select
                                id="assigned_user_id"
                                v-model="form.assigned_user_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            >
                                <option value="">Select a user</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.assigned_user_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.assigned_user_id }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a
                            :href="route('leads.show', lead.id)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg disabled:opacity-50"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Lead' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernLayout>
</template>
