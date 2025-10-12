<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    lead: Object,
    // callDetail: Object, // Optional: if completing an existing call
});

const emit = defineEmits(['close']);

const leadDetails = ref([]);

// Function to fetch lead details via API (fallback)
const fetchLeadDetailsViaAPI = async (leadId) => {
    if (!leadId) return;

    try {
        const response = await fetch(`/api/leads/${leadId}/call-history-public`);

        if (response.ok) {
            const data = await response.json();
            leadDetails.value = data.lead_details || [];
        } else {
            leadDetails.value = [];
        }
    } catch (error) {
        leadDetails.value = [];
    }
};

const form = useForm({
    lead_id: null,
    call_followup_date: new Date().toISOString().split('T')[0],
    call_followup_summary: '',
    next_call_date: '',
});

// Watch for lead prop changes and update form
watch(() => props.lead, (newLead) => {
    if (newLead && newLead.id) {
        form.lead_id = newLead.id;

        // Use lead details if available, otherwise fetch via API
        if (newLead.lead_details && newLead.lead_details.length > 0) {
            leadDetails.value = newLead.lead_details;
        } else {
            fetchLeadDetailsViaAPI(newLead.id);
        }
    } else {
        leadDetails.value = [];
    }
}, { immediate: true });


// Watch for callDetail prop changes (for completing existing calls)
watch(() => props.callDetail, (newCallDetail) => {
    if (newCallDetail) {
        form.call_followup_summary = newCallDetail.call_followup_summary || '';
        form.next_call_date = newCallDetail.next_call_date ? new Date(newCallDetail.next_call_date).toISOString().split('T')[0] : '';
    }
}, { immediate: true });

const submitCall = () => {
    if (!form.lead_id) {
        return;
    }

    // If we have a callDetail, we're completing an existing call
    if (props.callDetail) {
        form.patch(route('lead-details.complete', props.callDetail.id), {
            onSuccess: () => {
                emit('close');
                form.reset();
                // Reset to today's date after successful submission
                form.call_followup_date = new Date().toISOString().split('T')[0];
            },
            onError: (errors) => {
                // Handle errors silently
            }
        });
    } else {
        // Creating a new call record
        form.post(route('lead-details.store'), {
            onSuccess: () => {
                emit('close');
                form.reset();
                // Reset to today's date after successful submission
                form.call_followup_date = new Date().toISOString().split('T')[0];
            },
            onError: (errors) => {
                // Handle errors silently
            }
        });
    }
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <!-- Header -->
                    <div class="sm:flex sm:items-start mb-6">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                {{ callDetail ? 'Complete Call' : 'Call Lead' }}: {{ lead?.name || 'Loading...' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ lead?.company_name || 'Loading...' }} • {{ lead?.phone || 'Loading...' }} • {{ lead?.email || 'Loading...' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Call Form -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-2">
                                {{ callDetail ? 'Complete Call Details' : 'New Call Details' }}
                            </h4>

                            <form @submit.prevent="submitCall" class="space-y-4">
                                <div>
                                    <label for="call_followup_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Call Date *
                                    </label>
                                    <input
                                        type="date"
                                        id="call_followup_date"
                                        v-model="form.call_followup_date"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.call_followup_date }"
                                        required
                                    />
                                    <div v-if="form.errors.call_followup_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.call_followup_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="call_followup_summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Call Summary *
                                    </label>
                                    <textarea
                                        id="call_followup_summary"
                                        v-model="form.call_followup_summary"
                                        rows="4"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.call_followup_summary }"
                                        placeholder="Enter call details and outcome..."
                                        required
                                    ></textarea>
                                    <div v-if="form.errors.call_followup_summary" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.call_followup_summary }}
                                    </div>
                                </div>

                                <div>
                                    <label for="next_call_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Next Call Date (Optional)
                                    </label>
                                    <input
                                        type="date"
                                        id="next_call_date"
                                        v-model="form.next_call_date"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    />
                                    <div v-if="form.errors.next_call_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.next_call_date }}
                                    </div>
                                </div>

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
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    >
                                        {{ form.processing ? 'Saving...' : (callDetail ? 'Complete Call' : 'Save Call Details') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Right Column: Call History -->
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-2">
                                Latest Call History
                            </h4>

                            <div v-if="leadDetails && leadDetails.length > 0" class="space-y-3">
                                <div
                                    v-for="(detail, index) in leadDetails.slice(0, 3)"
                                    :key="detail.id"
                                    class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900 border-blue-200 dark:border-blue-700': index === 0 }"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded">
                                                Call #{{ leadDetails.length - index }}
                                            </span>
                                            <span v-if="index === 0" class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded">
                                                Latest
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ new Date(detail.call_followup_date).toLocaleDateString() }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                        {{ detail.call_followup_summary }}
                                    </p>

                                    <div v-if="detail.next_call_date" class="text-xs text-gray-500 dark:text-gray-400">
                                        Next call: {{ new Date(detail.next_call_date).toLocaleDateString() }}
                                    </div>

                                    <div v-if="detail.called_at" class="text-xs text-green-600 dark:text-green-400 mt-1">
                                        ✓ Completed on {{ new Date(detail.called_at).toLocaleDateString() }}
                                    </div>
                                    <div v-else class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                        ⏳ Pending
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <p class="mt-2 text-sm">No call history available</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">This will be the first call for this lead</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
