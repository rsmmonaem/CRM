<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    callDetail: Object,
    lead: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    call_followup_date: new Date().toISOString().split('T')[0],
    call_followup_summary: '',
    next_call_date: '',
});

// Watch for callDetail prop changes and update form
watch(() => props.callDetail, (newCallDetail) => {
    if (newCallDetail) {
        // Format dates properly for HTML date inputs
        form.call_followup_date = newCallDetail.call_followup_date ?
            newCallDetail.call_followup_date.split('T')[0] :
            new Date().toISOString().split('T')[0];

        form.call_followup_summary = newCallDetail.call_followup_summary || '';

        form.next_call_date = newCallDetail.next_call_date ?
            newCallDetail.next_call_date.split('T')[0] : '';
    }
}, { immediate: true });

const submitEdit = () => {
    if (!props.callDetail?.id) {
        console.error('No call detail ID available');
        return;
    }

    form.put(route('lead-details.update', props.callDetail.id), {
        onSuccess: () => {
            emit('close');
        },
        onError: (errors) => {
            console.error('Call edit errors:', errors);
        }
    });
};
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Edit Call: {{ lead?.name }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ lead?.company_name }} • {{ lead?.phone }} • {{ lead?.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitEdit" class="mt-6">
                        <div class="space-y-4">
                            <!-- Debug info (remove in production) -->
                            <div v-if="!callDetail?.id" class="p-3 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm text-red-800">⚠️ No call detail ID available. Please close and try again.</p>
                            </div>

                            <div>
                                <label for="edit_call_followup_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Call Date *
                                </label>
                                <input
                                    type="date"
                                    id="edit_call_followup_date"
                                    v-model="form.call_followup_date"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    :class="{ 'border-red-500': form.errors.call_followup_date }"
                                    required
                                />
                                <div v-if="form.errors.call_followup_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.call_followup_date }}
                                </div>
                            </div>

                            <div>
                                <label for="edit_call_followup_summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Call Summary *
                                </label>
                                <textarea
                                    id="edit_call_followup_summary"
                                    v-model="form.call_followup_summary"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    :class="{ 'border-red-500': form.errors.call_followup_summary }"
                                    placeholder="Enter call details and outcome..."
                                    required
                                ></textarea>
                                <div v-if="form.errors.call_followup_summary" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.call_followup_summary }}
                                </div>
                            </div>

                            <div>
                                <label for="edit_next_call_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Next Call Date (Optional)
                                </label>
                                <input
                                    type="date"
                                    id="edit_next_call_date"
                                    v-model="form.next_call_date"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    :class="{ 'border-red-500': form.errors.next_call_date }"
                                />
                                <div v-if="form.errors.next_call_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.next_call_date }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Call Details' }}
                            </button>
                            <button
                                type="button"
                                @click="emit('close')"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
