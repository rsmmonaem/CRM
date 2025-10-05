<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    services: Array,
    statuses: Array,
    users: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    company_name: '',
    location: '',
    service_id: '',
    status_id: '',
    assigned_user_id: '',
});

const submitLead = () => {
    form.post(route('leads.store'), {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
        onError: (errors) => {
            // Errors will be automatically displayed by Inertia
            console.log('Validation errors:', errors);
        }
    });
};

const resetForm = () => {
    form.name = '';
    form.phone = '';
    form.email = '';
    form.company_name = '';
    form.location = '';
    form.service_id = '';
    form.status_id = '';
    form.assigned_user_id = '';
    form.clearErrors();
};

// Reset form when modal is closed
watch(() => props.show, (newValue) => {
    if (!newValue) {
        resetForm();
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <!-- Header -->
                    <div class="sm:flex sm:items-start mb-6">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Add New Lead
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Create a new lead with contact information
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submitLead" class="space-y-4">
                        <!-- General Error Display -->
                        <div v-if="form.hasErrors" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                        Please correct the following errors:
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li v-for="(error, field) in form.errors" :key="field">
                                                {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Name (Optional)
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="form.name"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.name }"
                                    placeholder="Enter lead name"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Phone Number
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    v-model="form.phone"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                    placeholder="Enter phone number"
                                />
                                <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email Address
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.email }"
                                    placeholder="Enter email address"
                                />
                                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Contact Info Note -->
                            <!-- <div class="md:col-span-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="text-red-500">*</span> At least one contact method (phone or email) is required.
                                </p>
                            </div> -->

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
                                    :class="{ 'border-red-500': form.errors.company_name }"
                                    placeholder="Enter company name"
                                />
                                <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.company_name }}
                                </div>
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Location (Optional)
                                </label>
                                <input
                                    type="text"
                                    id="location"
                                    v-model="form.location"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.location }"
                                    placeholder="Enter location"
                                />
                                <div v-if="form.errors.location" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.location }}
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
                                    :class="{ 'border-red-500': form.errors.service_id }"
                                    required
                                >
                                    <option value="">Select a service</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.service_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
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
                                    :class="{ 'border-red-500': form.errors.status_id }"
                                    required
                                >
                                    <option value="">Select a status</option>
                                    <option v-for="status in statuses" :key="status.id" :value="status.id">
                                        {{ status.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.status_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.status_id }}
                                </div>
                            </div>

                            <!-- Assigned User -->
                            <div>
                                <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Assigned User *
                                </label>
                                <select
                                    id="assigned_user_id"
                                    v-model="form.assigned_user_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.assigned_user_id }"
                                    required
                                >
                                    <option value="">Select a user</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.assigned_user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.assigned_user_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="emit('close')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Lead' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
