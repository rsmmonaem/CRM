<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    lead: Object,
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

// Watch for lead prop changes and update form
watch(() => props.lead, (newLead) => {
    if (newLead) {
        form.name = newLead.name || '';
        form.phone = newLead.phone || '';
        form.email = newLead.email || '';
        form.company_name = newLead.company_name || '';
        form.location = newLead.location || '';
        form.service_id = newLead.service_id || '';
        form.status_id = newLead.status_id || '';
        form.assigned_user_id = newLead.assigned_user_id || '';
    }
}, { immediate: true });

const submitLead = () => {
    form.put(route('leads.update', props.lead.id), {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
        onError: (errors) => {
            // Handle errors silently
        }
    });
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <!-- Modern Header -->
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    Edit Lead
                                </h3>
                                <p class="text-yellow-100 text-sm">{{ lead?.name || 'Loading...' }}</p>
                            </div>
                        </div>
                        <button @click="emit('close')" class="text-white hover:text-yellow-200 p-2 rounded-lg hover:bg-white hover:bg-opacity-20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 px-6 py-6">

                    <!-- Form -->
                    <form @submit.prevent="submitLead" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label for="name" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Customer Name</span>
                                    <span class="text-gray-400 text-xs">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.name }"
                                        placeholder="Enter customer name"
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.name }}</span>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <label for="phone" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>Phone Number</span>
                                    <span class="text-red-500 text-xs">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="tel"
                                        id="phone"
                                        v-model="form.phone"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.phone }"
                                        placeholder="Enter phone number"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.phone" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.phone }}</span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Email Address</span>
                                    <span class="text-red-500 text-xs">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="email"
                                        id="email"
                                        v-model="form.email"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.email }"
                                        placeholder="Enter email address"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.email }}</span>
                                </div>
                            </div>

                            <!-- Company Name -->
                            <div class="space-y-2">
                                <label for="company_name" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>Company Name</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="company_name"
                                        v-model="form.company_name"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.company_name }"
                                        placeholder="Enter company name"
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.company_name" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.company_name }}</span>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="space-y-2">
                                <label for="location" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>Location</span>
                                    <span class="text-gray-400 text-xs">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="location"
                                        v-model="form.location"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.location }"
                                        placeholder="Enter location"
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.location" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.location }}</span>
                                </div>
                            </div>

                            <!-- Service -->
                            <div class="space-y-2">
                                <label for="service_id" class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                    <span>Service</span>
                                    <span class="text-red-500 text-xs">*</span>
                                </label>
                                <div class="relative">
                                    <select
                                        id="service_id"
                                        v-model="form.service_id"
                                        class="w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors appearance-none"
                                        :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.service_id }"
                                        required
                                    >
                                        <option value="">Select a service</option>
                                        <option v-for="service in services" :key="service.id" :value="service.id">
                                            {{ service.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.service_id" class="text-sm text-red-600 dark:text-red-400 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ form.errors.service_id }}</span>
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
                                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Lead' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
