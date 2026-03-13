<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CallModal from '@/Components/CallModal.vue';
import EditCallModal from '@/Components/EditCallModal.vue';
import ViewCallModal from '@/Components/ViewCallModal.vue';
import EditLeadModal from '@/Components/EditLeadModal.vue';

const props = defineProps({
    lead: Object,
    user: Object,
    statuses: Array,
    call_statuses: Array,
    services: Array,
    users: Array,
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
const showEditLeadModal = ref(false);
const selectedCallDetail = ref(null);

const openCallModal = (lead) => {
    showCallModal.value = true;
};

const openAddCallModal = () => {
    showCallModal.value = true;
};

const closeCallModal = () => {
    showCallModal.value = false;
    showAddCallModal.value = false;
    showEditCallModal.value = false;
    showViewCallModal.value = false;
    showEditLeadModal.value = false;
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
                    <button
                        v-if="canManageLeadDetails(lead)"
                        @click="showEditLeadModal = true"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Edit Lead
                    </button>
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
                                        <!-- <button
                                            v-if="canManageLeadDetails(lead)"
                                            @click="openCallModal(lead)"
                                            class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-xs"
                                        >
                                            📞 Call
                                        </button> -->
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
            :statuses="statuses"
            :call-statuses="call_statuses"
            @close="closeCallModal"
        />

        <!-- Edit Call Modal -->
        <EditCallModal
            v-if="showEditCallModal"
            :callDetail="selectedCallDetail"
            :lead="lead"
            :call-statuses="call_statuses"
            @close="closeCallModal"
        />

        <!-- View Call Modal -->
        <ViewCallModal
            v-if="showViewCallModal"
            :callDetail="selectedCallDetail"
            :lead="lead"
            @close="closeCallModal"
        />

        <!-- Edit Lead Modal -->
        <EditLeadModal
            v-if="showEditLeadModal"
            :lead="lead"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeCallModal"
        />


    </ModernLayout>
</template>

