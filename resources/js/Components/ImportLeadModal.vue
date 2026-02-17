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
});

const emit = defineEmits(['close']);

const form = useForm({
    file: null,
    service_id: '',
    status_id: '',
});

const fileInput = ref(null);

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submitImport = () => {
    form.post('/leads/import', {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
        onError: (errors) => {
            console.log('Import errors:', errors);
        }
    });
};

const resetForm = () => {
    form.file = null;
    form.service_id = '';
    form.status_id = '';
    if (fileInput.value) fileInput.value.value = '';
    form.clearErrors();
};

watch(() => props.show, (newValue) => {
    if (!newValue) {
        resetForm();
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" @click="emit('close')"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start mb-6">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Import Leads from CSV
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Upload a CSV file with lead information. Only phone number is required.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitImport" class="space-y-4">
                        <!-- CSV Structure Note -->
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                            <div class="flex items-start justify-between">
                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                    <strong>CSV Format:</strong> Name, Company, Phone, Email
                                    <br>
                                    If the CSV has only one column, it will be treated as Phone.
                                    <br>
                                    <em>Phone number is mandatory.</em>
                                </p>
                                <a
                                    href="/samples/leads_sample.csv"
                                    download="leads_sample.csv"
                                    class="inline-flex items-center px-2 py-1 border border-blue-600 dark:border-blue-400 rounded text-[10px] font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-400 dark:hover:text-gray-900 transition-colors duration-200"
                                >
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    Sample CSV
                                </a>
                            </div>
                        </div>

                        <!-- File Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                CSV File *
                            </label>
                            <input
                                type="file"
                                ref="fileInput"
                                @change="handleFileChange"
                                accept=".csv"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-200"
                                required
                            />
                            <div v-if="form.errors.file" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.file }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Default Service -->
                            <div>
                                <label for="import_service_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Default Service *
                                </label>
                                <select
                                    id="import_service_id"
                                    v-model="form.service_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    required
                                >
                                    <option value="">Select a service</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">
                                        {{ service.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Default Status -->
                            <div>
                                <label for="import_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Default Status *
                                </label>
                                <select
                                    id="import_status_id"
                                    v-model="form.status_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    required
                                >
                                    <option value="">Select a status</option>
                                    <option v-for="status in statuses" :key="status.id" :value="status.id">
                                        {{ status.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
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
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Importing...' : 'Start Import' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
