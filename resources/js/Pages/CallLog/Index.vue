<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModernLayout from '@/Layouts/ModernLayout.vue';
import EditCallModal from '@/Components/EditCallModal.vue';

const props = defineProps({
    callLogs: Object,
    user: Object,
});

const showEditModal = ref(false);
const editingCallDetail = ref(null);

const openEditModal = (callLog) => {
    editingCallDetail.value = callLog;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingCallDetail.value = null;
};

const getStatusClass = (status) => {
    const statusClasses = {
        'New': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'Contacted': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'Qualified': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'Proposal': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'Negotiation': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'Closed Won': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
        'Closed Lost': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};

const getCallStatus = (callLog) => {
    if (callLog.called_at) {
        return 'Completed';
    }
    return 'Scheduled';
};

const getCallStatusClass = (callLog) => {
    if (callLog.called_at) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
};
</script>

<template>
    <Head title="Call Log" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-green-600 via-teal-600 to-blue-600 rounded-xl p-8 text-white shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Call Log</h1>
                        <p class="text-green-100 text-lg">Track and manage all call activities</p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-2xl font-bold">{{ callLogs.total }}</div>
                            <div class="text-green-100 text-sm">Total Calls</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Log Section -->
        <div class="dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center space-x-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>All Call Logs</span>
                </h3>
                <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full">{{ callLogs.total }} total</span>
            </div>

            <!-- Empty State -->
            <div v-if="callLogs.data.length === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg">No call logs found.</p>
            </div>

            <!-- Call Log Cards -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="callLog in callLogs.data" :key="callLog.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:hover:shadow-white/20 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-white/30 transition-all duration-300 p-4 flex flex-col justify-between">

                    <!-- Header: Lead Name + Call Status -->
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white truncate">
                            {{ callLog.lead?.name || 'Unnamed Lead' }}
                        </h4>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full" :class="getCallStatusClass(callLog)">
                            {{ getCallStatus(callLog) }}
                        </span>
                    </div>

                    <!-- Call Info Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300 mb-2">
                        <div class="truncate"><strong>Lead Status:</strong> {{ callLog.lead?.status?.name || '-' }}</div>
                        <div class="truncate"><strong>Service:</strong> {{ callLog.lead?.service?.name || '-' }}</div>
                        <div class="truncate"><strong>Assigned:</strong> {{ callLog.lead?.assigned_user?.name || '-' }}</div>
                        <div class="truncate"><strong>Created:</strong> {{ callLog.created_at ? new Date(callLog.created_at).toLocaleDateString() : '-' }}</div>
                        <div class="truncate"><strong>By:</strong> {{ callLog.creator?.name || '-' }}</div>
                    </div>

                    <!-- Call Summary -->
                    <div v-if="callLog.call_followup_summary" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-300">
                        <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Call Summary</div>
                        <div class="text-gray-700 dark:text-gray-200 break-words">
                            {{ callLog.call_followup_summary }}
                        </div>
                    </div>

                    <!-- Call Details -->
                    <div class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-700 text-xs text-blue-600 dark:text-blue-300">
                        <div class="grid grid-cols-2 gap-2">
                            <div><strong>Call Date:</strong> {{ callLog.call_followup_date ? new Date(callLog.call_followup_date).toLocaleDateString() : '-' }}</div>
                            <div><strong>Next Call:</strong> {{ callLog.next_call_date ? new Date(callLog.next_call_date).toLocaleDateString() : '-' }}</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Link v-if="callLog.lead?.id" :href="route('leads.show', callLog.lead.id)" class="flex-1 bg-blue-500 hover:bg-blue-600 dark:hover:bg-blue-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">View Lead</Link>
                        <button @click="openEditModal(callLog)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 dark:hover:bg-yellow-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">Edit Call</button>
                    </div>

                </div>
            </div>

            <!-- Pagination -->
            <div v-if="callLogs.links && callLogs.links.length > 3" class="mt-8 flex justify-center">
                <nav class="flex items-center space-x-2">
                    <template v-for="link in callLogs.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                link.active
                                    ? 'bg-blue-500 text-white'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-2 text-sm font-medium rounded-md text-gray-400 dark:text-gray-600 cursor-not-allowed"
                        />
                    </template>
                </nav>
            </div>
        </div>

        <!-- Edit Call Modal -->
        <EditCallModal
            v-if="showEditModal"
            :callDetail="editingCallDetail"
            @close="closeEditModal"
        />
    </ModernLayout>
</template>
