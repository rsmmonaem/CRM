<script setup>
import { computed } from 'vue';

const props = defineProps({
    callDetail: Object,
    lead: Object,
});

const emit = defineEmits(['close']);

const formatDate = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Call Details: {{ lead?.name }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ lead?.company_name }} • {{ lead?.phone }} • {{ lead?.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div v-if="callDetail" class="space-y-6">
                            <!-- Call Date -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Call Date</h4>
                                <p class="text-lg text-gray-700 dark:text-gray-300">{{ formatDate(callDetail.call_followup_date) }}</p>
                            </div>

                            <!-- Call Summary -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Call Summary</h4>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ callDetail.call_followup_summary }}</p>
                            </div>

                            <!-- Next Call Date -->
                            <div v-if="callDetail.next_call_date" class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Next Call Scheduled</h4>
                                <p class="text-lg text-blue-700 dark:text-blue-300">{{ formatDate(callDetail.next_call_date) }}</p>
                            </div>

                            <!-- Call Creator -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Recorded By</h4>
                                <p class="text-gray-700 dark:text-gray-300">{{ callDetail.creator?.name || 'Unknown' }}</p>
                            </div>

                            <!-- Created At -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Recorded On</h4>
                                <p class="text-gray-700 dark:text-gray-300">{{ formatDateTime(callDetail.created_at) }}</p>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No call details available.</p>
                        </div>
                    </div>

                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            @click="emit('close')"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

