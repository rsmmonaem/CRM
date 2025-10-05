<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CallModal from '@/Components/CallModal.vue';
import EditCallModal from '@/Components/EditCallModal.vue';
import ViewCallModal from '@/Components/ViewCallModal.vue';

const props = defineProps({
    lead: Object,
    user: Object,
});

const hasPermission = (module, action) => {
    if (props.user?.is_admin) return true;
    return props.user?.permissions?.[module]?.includes(action) || false;
};

const canManageLeadDetails = (lead) => {
    // Admin users can manage all lead details
    if (props.user?.is_admin) return true;
    
    // Users with leads.create permission can manage lead details
    if (hasPermission('leads', 'create')) return true;
    
    // Users can manage details for leads assigned to them
    return lead.assigned_user_id === props.user?.id;
};

const showCallModal = ref(false);
const showAddCallModal = ref(false);
const showEditCallModal = ref(false);
const showViewCallModal = ref(false);
const selectedCallDetail = ref(null);

const openCallModal = (lead) => {
    showCallModal.value = true;
};

const openAddCallModal = () => {
    showAddCallModal.value = true;
};

const closeCallModal = () => {
    showCallModal.value = false;
    showAddCallModal.value = false;
    showEditCallModal.value = false;
    showViewCallModal.value = false;
    selectedCallDetail.value = null;
};

const openEditCallModal = (callDetail) => {
    selectedCallDetail.value = callDetail;
    showEditCallModal.value = true;
};

const openViewCallModal = (callDetail) => {
    selectedCallDetail.value = callDetail;
    showViewCallModal.value = true;
};

const form = useForm({
    lead_id: props.lead.id,
    call_followup_date: new Date().toISOString().split('T')[0],
    call_followup_summary: '',
    next_call_date: '',
});

const submitCall = () => {
    form.post(route('lead-details.store'), {
        onSuccess: () => {
            closeCallModal();
            form.reset();
            // Reset form with lead_id and today's date
            form.lead_id = props.lead.id;
            form.call_followup_date = new Date().toISOString().split('T')[0];
        },
        onError: (errors) => {
            console.error('Call submission errors:', errors);
        }
    });
};

const deleteCallDetail = (callDetail) => {
    if (confirm(`Are you sure you want to delete this call record from ${new Date(callDetail.call_followup_date).toLocaleDateString()}?`)) {
        router.delete(route('lead-details.destroy', callDetail.id), {
            onSuccess: () => {
                // The page will automatically refresh with updated data
            },
            onError: (errors) => {
                console.error('Delete call detail errors:', errors);
            }
        });
    }
};
</script>

<template>
    <Head :title="`Lead: ${lead.name}`" />

    <ModernLayout>
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Lead Details: {{ lead.name }}
                </h2>
                <div class="flex space-x-2">
                    <button
                        v-if="canManageLeadDetails(lead)"
                        @click="openAddCallModal"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Add Call
                    </button>
                    <Link
                        :href="route('leads.edit', lead.id)"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Edit Lead
                    </Link>
                    <Link
                        :href="route('leads.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Back to Leads
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Lead Information -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lead Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.company_name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.service.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ lead.status.name }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned To</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.assigned_user.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created By</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ lead.creator.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created At</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ new Date(lead.created_at).toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call History -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Call History</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="lead.lead_details.length === 0" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No call history found.</p>
                            <button
                                v-if="canManageLeadDetails(lead)"
                                @click="openAddCallModal"
                                class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add First Call
                            </button>
                        </div>
                        <div v-else class="space-y-4">
                            <div 
                                v-for="call in lead.lead_details" 
                                :key="call.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                Call on {{ new Date(call.call_followup_date).toLocaleDateString() }}
                                            </h4>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                by {{ call.creator.name }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ call.call_followup_summary }}</p>
                                        <div v-if="call.next_call_date" class="mt-2">
                                            <p class="text-sm text-blue-600 dark:text-blue-400">
                                                Next call scheduled: {{ new Date(call.next_call_date).toLocaleDateString() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            @click="openViewCallModal(call)"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs"
                                        >
                                            👁️ View
                                        </button>
                                        <button
                                            v-if="canManageLeadDetails(lead)"
                                            @click="openEditCallModal(call)"
                                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-xs"
                                        >
                                            ✏️ Edit
                                        </button>
                                        <button
                                            v-if="canManageLeadDetails(lead)"
                                            @click="openCallModal(lead)"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs"
                                        >
                                            📞 Call Again
                                        </button>
                                        <button
                                            v-if="canManageLeadDetails(lead)"
                                            @click="deleteCallDetail(call)"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs"
                                        >
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Modal -->
        <CallModal 
            v-if="showCallModal"
            :lead="lead"
            @close="closeCallModal"
        />

        <!-- Edit Call Modal -->
        <EditCallModal 
            v-if="showEditCallModal"
            :callDetail="selectedCallDetail"
            :lead="lead"
            @close="closeCallModal"
        />

        <!-- View Call Modal -->
        <ViewCallModal 
            v-if="showViewCallModal"
            :callDetail="selectedCallDetail"
            :lead="lead"
            @close="closeCallModal"
        />

        <!-- Add Call Modal -->
        <div v-if="showAddCallModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCallModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                    Add Call for: {{ lead.name }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ lead.company_name }} • {{ lead.phone }} • {{ lead.email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitCall" class="mt-6">
                            <div class="space-y-4">
                                <!-- Debug info (remove in production) -->
                                <div v-if="form.errors.lead_id" class="p-3 bg-red-50 border border-red-200 rounded-md">
                                    <p class="text-sm text-red-800">⚠️ {{ form.errors.lead_id }}</p>
                                </div>
                                
                                <!-- Call Date -->
                                <div>
                                    <label for="call_followup_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Call Date *
                                    </label>
                                    <input
                                        type="date"
                                        id="call_followup_date"
                                        v-model="form.call_followup_date"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        :class="{ 'border-red-500': form.errors.call_followup_date }"
                                        required
                                    />
                                    <div v-if="form.errors.call_followup_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.call_followup_date }}
                                    </div>
                                </div>

                                <!-- Call Summary -->
                                <div>
                                    <label for="call_followup_summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Call Summary *
                                    </label>
                                    <textarea
                                        id="call_followup_summary"
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

                                <!-- Next Call Date -->
                                <div>
                                    <label for="next_call_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Next Call Date (Optional)
                                    </label>
                                    <input
                                        type="date"
                                        id="next_call_date"
                                        v-model="form.next_call_date"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    />
                                    <div v-if="form.errors.next_call_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.next_call_date }}
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="mt-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Call Details' }}
                                </button>
                                <button
                                    type="button"
                                    @click="closeCallModal"
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
    </ModernLayout>
</template>

