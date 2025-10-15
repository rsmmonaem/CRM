<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import EditLeadModal from './EditLeadModal.vue';
import CallModal from './CallModal.vue';
import ViewCallModal from './ViewCallModal.vue';
import EditCallModal from './EditCallModal.vue';

const props = defineProps({
    lead: Object,
    show: Boolean,
    services: Array,
    statuses: Array,
    users: Array,
    user: Object
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Permission checking functions
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

// Lead Edit Modal state
const showEditModal = ref(false);

const openEditModal = () => {
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

// Call Modal states
const showCallModal = ref(false);
const showViewCallModal = ref(false);
const showEditCallModal = ref(false);
const selectedCallDetail = ref(null);

const openCallModal = (lead) => {
    showCallModal.value = true;
};

const openViewCallModal = (callDetail) => {
    selectedCallDetail.value = callDetail;
    showViewCallModal.value = true;
};

const openEditCallModal = (callDetail) => {
    selectedCallDetail.value = callDetail;
    showEditCallModal.value = true;
};

const closeCallModals = () => {
    showCallModal.value = false;
    showViewCallModal.value = false;
    showEditCallModal.value = false;
    selectedCallDetail.value = null;
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

// Computed properties for better data handling
const leadDetails = computed(() => {
    return props.lead?.lead_details && props.lead.lead_details.length > 0
        ? props.lead.lead_details[0]
        : null;
});

const nextCallDate = computed(() => {
    return leadDetails.value?.next_call_date
        ? new Date(leadDetails.value.next_call_date).toLocaleDateString()
        : 'Not set';
});

const lastCallDate = computed(() => {
    return leadDetails.value?.called_at
        ? new Date(leadDetails.value.called_at).toLocaleDateString()
        : 'Never';
});

const formatPhone = (phone) => {
    if (!phone) return 'N/A';
    // Simple phone formatting - you can enhance this
    return phone;
};

const formatEmail = (email) => {
    if (!email) return 'N/A';
    return email;
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
        <div class="relative top-10 mx-auto p-4 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 max-w-6xl shadow-xl rounded-lg bg-white dark:bg-gray-800" @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ lead?.name || 'Lead Details' }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ lead?.company_name || 'Company' }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button @click="openEditModal"
                            class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md transition-colors flex items-center space-x-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Edit Lead</span>
                    </button>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="space-y-4">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Phone</label>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatPhone(lead?.phone) }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</label>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatEmail(lead?.email) }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Location</label>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lead?.location || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Status and Service Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <label class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide">Status</label>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ lead?.status?.name || 'No Status' }}
                        </span>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <label class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">Service</label>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            {{ lead?.service?.name || 'No Service' }}
                        </span>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <label class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide">Assigned To</label>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lead?.assigned_user?.name || 'Unassigned' }}</p>
                    </div>
                </div>

                <!-- Call Information -->
                <div v-if="leadDetails" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100">Latest Call Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-gray-800 p-3 rounded-md">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Next Call Date</label>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ nextCallDate }}</p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-3 rounded-md">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Last Call Date</label>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lastCallDate }}</p>
                        </div>

                        <div class="md:col-span-2 bg-white dark:bg-gray-800 p-3 rounded-md">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Call Summary</label>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ leadDetails.call_followup_summary || 'No summary available' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Call History -->
                <div v-if="lead?.lead_details && lead.lead_details.length > 0" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Call History</h4>
                    </div>
                    <div class="space-y-3 max-h-60 overflow-y-auto">
                        <div v-for="(detail, index) in lead.lead_details.slice().reverse()" :key="detail.id"
                             class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-3 rounded-lg shadow-sm">

                            <!-- Call Header -->
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Call on {{ detail.called_at ? new Date(detail.called_at).toLocaleDateString() : 'Not called' }}
                                    </h5>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ lead.assigned_user?.name || 'Unknown User' }}
                                    </p>
                                </div>
                                <span class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded-full">
                                    #{{ lead.lead_details.length - index }}
                                </span>
                            </div>

                            <!-- Call Summary -->
                            <div class="mb-2">
                                <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-600 p-2 rounded-md">
                                    {{ detail.call_followup_summary || 'No summary available' }}
                                </p>
                            </div>

                            <!-- Next Call Info -->
                            <div v-if="detail.next_call_date" class="mb-2">
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Next call scheduled:</span> {{ new Date(detail.next_call_date).toLocaleDateString() }}
                                </p>
                            </div>

                            <!-- Call Details -->
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                                <span>Duration: {{ detail.call_duration || 'N/A' }}</span>
                                <span v-if="detail.called_at">Called: {{ new Date(detail.called_at).toLocaleString() }}</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-1 pt-2 border-t border-gray-200 dark:border-gray-600">
                                <button
                                    @click="openViewCallModal(detail)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    title="View">
                                    👁️ View
                                </button>
                                <button
                                    v-if="canManageLeadDetails(lead)"
                                    @click="openEditCallModal(detail)"
                                    class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 text-xs font-medium px-2 py-1 rounded hover:bg-yellow-50 dark:hover:bg-yellow-900/20"
                                    title="Edit">
                                    ✏️ Edit
                                </button>
                                <button
                                    v-if="canManageLeadDetails(lead)"
                                    @click="openCallModal(lead)"
                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-xs font-medium px-2 py-1 rounded hover:bg-green-50 dark:hover:bg-green-900/20"
                                    title="Call Again">
                                    📞 Call Again
                                </button>
                                <button
                                    v-if="canManageLeadDetails(lead)"
                                    @click="deleteCallDetail(detail)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20"
                                    title="Delete">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Call Details -->
                <div v-else class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <div class="text-center py-4">
                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No call history</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This lead hasn't been called yet.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end items-center mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-2">
                    <button @click="closeModal"
                            class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors text-sm">
                        Close
                    </button>
                    <a :href="route('leads.show', lead?.id)"
                       class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition-colors text-sm">
                        View Full Lead
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Edit Modal -->
    <EditLeadModal
        v-if="showEditModal"
        :lead="lead"
        :services="services"
        :statuses="statuses"
        :users="users"
        @close="closeEditModal"
    />

    <!-- Call Modals -->
    <CallModal
        v-if="showCallModal"
        :lead="lead"
        :statuses="statuses"
        @close="closeCallModals"
    />

    <ViewCallModal
        v-if="showViewCallModal"
        :callDetail="selectedCallDetail"
        :lead="lead"
        @close="closeCallModals"
    />

    <EditCallModal
        v-if="showEditCallModal"
        :callDetail="selectedCallDetail"
        :lead="lead"
        @close="closeCallModals"
    />
</template>
