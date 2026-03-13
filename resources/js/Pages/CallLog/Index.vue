<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ModernLayout from '@/Layouts/ModernLayout.vue';
import EditCallModal from '@/Components/EditCallModal.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    callLogs: Array,
    user: Object,
    services: Array,
    statuses: Array,
    call_statuses: Array,
    users: Array,
});

const showEditModal = ref(false);
const editingCallDetail = ref(null);

// Filter state
const filters = ref({
    search: '',
    service: '',
    status: '', // Lead Status
    call_status: '', // Interaction Status
    dateFrom: '',
    dateTo: '',
    user: '',
    log_by: ''
});

const openEditModal = (callLog) => {
    editingCallDetail.value = callLog;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingCallDetail.value = null;
};

const deleteCallLog = (callLog) => {
    Swal.fire({
        title: 'Delete Call Log',
        text: `Are you sure you want to delete this call log?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('lead-details.destroy', callLog.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Call log has been deleted.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });

};

// Selection state
const selectedIds = ref([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = filteredCallLogs.value.map(log => log.id);
    } else {
        selectedIds.value = [];
    }
};

const handleSelect = () => {
    if (selectedIds.value.length === filteredCallLogs.value.length) {
        selectAll.value = true;
    } else {
        selectAll.value = false;
    }
};

const deleteBulk = () => {
    if (selectedIds.value.length === 0) return;

    Swal.fire({
        title: 'Bulk Delete Call Logs',
        text: `Are you sure you want to delete ${selectedIds.value.length} selected call logs?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('lead-details.bulk-destroy'), {
                ids: selectedIds.value
            }, {
                onSuccess: () => {
                    selectedIds.value = [];
                    selectAll.value = false;
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected call logs have been removed.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};

// Computed filtered call logs
const filteredCallLogs = computed(() => {
    let result = [...props.callLogs];

    // Search filter (Lead name, phone, email, company, summary)
    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        result = result.filter(log => 
            log.lead?.name?.toLowerCase().includes(search) ||
            log.lead?.phone?.toLowerCase().includes(search) ||
            log.lead?.email?.toLowerCase().includes(search) ||
            log.lead?.company_name?.toLowerCase().includes(search) ||
            log.call_followup_summary?.toLowerCase().includes(search)
        );
    }

    // Service filter
    if (filters.value.service) {
        result = result.filter(log => log.lead?.service_id == filters.value.service);
    }

    // Lead Status filter
    if (filters.value.status) {
        result = result.filter(log => log.lead?.status_id == filters.value.status);
    }
    
    // Call Status filter
    if (filters.value.call_status) {
        result = result.filter(log => log.call_status == filters.value.call_status);
    }

    // Date range filter
    if (filters.value.dateFrom) {
        const fromDate = new Date(filters.value.dateFrom);
        result = result.filter(log => new Date(log.call_followup_date) >= fromDate);
    }

    if (filters.value.dateTo) {
        const toDate = new Date(filters.value.dateTo);
        toDate.setHours(23, 59, 59, 999); // Include the entire day
        result = result.filter(log => new Date(log.call_followup_date) <= toDate);
    }

    // User filter (admin only)
    if (filters.value.user && props.user?.is_admin) {
        result = result.filter(log => log.lead?.assigned_user_id == filters.value.user);
    }

    // Logs By filter (admin only)
    if (filters.value.log_by && props.user?.is_admin) {
        result = result.filter(log => log.created_by == filters.value.log_by);
    }

    return result;
});

const hasActiveFilters = computed(() => {
    return filters.value.search ||
           filters.value.service ||
           filters.value.status ||
           filters.value.call_status ||
           filters.value.dateFrom ||
           filters.value.dateTo ||
           filters.value.user ||
           filters.value.log_by;
});

// Clear all filters
const clearFilters = () => {
    filters.value = {
        search: '',
        service: '',
        status: '',
        call_status: '',
        dateFrom: '',
        dateTo: '',
        user: '',
        log_by: ''
    };
};

// Get count for specific service
const getServiceCount = (serviceId) => {
    if (!serviceId) return props.callLogs.length;
    return props.callLogs.filter(log => log.lead?.service_id == serviceId).length;
};

// Get count for specific status
const getStatusCount = (statusId) => {
    if (!statusId) return props.callLogs.length;
    return props.callLogs.filter(log => log.lead?.status_id == statusId).length;
};

// Get count for specific user
const getUserCount = (userId) => {
    if (!userId) return props.callLogs.length;
    return props.callLogs.filter(log => log.lead?.assigned_user_id == userId).length;
};

// Get filter statistics
const filterStats = computed(() => {
    const stats = {
        byService: {},
        byStatus: {},
        byCallStatus: {},
        byUser: {}
    };

    props.callLogs.forEach(log => {
        // Service stats
        const serviceName = log.lead?.service?.name || 'Unknown';
        stats.byService[serviceName] = (stats.byService[serviceName] || 0) + 1;

        // Lead Status stats
        const statusName = log.lead?.status?.name || 'Unknown';
        stats.byStatus[statusName] = (stats.byStatus[statusName] || 0) + 1;

        // Call Status stats
        const callStatusName = log.call_status || 'Unknown';
        stats.byCallStatus[callStatusName] = (stats.byCallStatus[callStatusName] || 0) + 1;

        // User stats
        const userName = log.lead?.assigned_user?.name || 'Unassigned';
        stats.byUser[userName] = (stats.byUser[userName] || 0) + 1;
    });

    return stats;
});

const getStatusClass = (status) => {
    const name = (status || '').toLowerCase();
    if (name.includes('won') || name.includes('success')) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    if (name.includes('lost') || name.includes('cancel')) return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    if (name.includes('contact')) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    if (name.includes('negotiation') || name.includes('deal')) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};

const getCallStatus = (callLog) => {
    return callLog.call_status || (callLog.called_at ? 'Completed' : 'Scheduled');
};

const getCallStatusClass = (callLog) => {
    const status = (callLog.call_status || '').toLowerCase();
    
    if (status.includes('not answer') || status.includes('busy') || status.includes('off') || status.includes('reachable')) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
    }
    if (status.includes('wrong') || status.includes('disconnect') || status.includes('not interested')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }
    if (status.includes('interested')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }
    if (status.includes('callback') || status.includes('scheduled')) {
        return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
    }
    
    if (callLog.called_at) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
};

const viewMode = ref(props.user?.default_view_mode || 'grid'); // Initialize from DB

const saveDefaultView = (mode) => {
    router.patch(route('profile.preference.update'), {
        default_view_mode: mode
    });
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
                            <div class="text-2xl font-bold">{{ hasActiveFilters ? filteredCallLogs.length : callLogs.length }}</div>
                            <div class="text-green-100 text-sm">Calls Showing</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                    </svg>
                    <span>Filters</span>
                </h3>
                <div class="flex items-center space-x-3">
                    <span v-if="hasActiveFilters" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                        {{ filteredCallLogs.length }} of {{ callLogs.length }} calls
                    </span>
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium flex items-center space-x-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>

            <!-- Filter Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4">
                <!-- Search Filter -->
                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Search Logs
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Search name or summary..."
                            class="w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
                        />
                    </div>
                </div>

                <!-- Service Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Service
                    </label>
                    <select
                        v-model="filters.service"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
                    >
                        <option value="">All Services</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }}
                        </option>
                    </select>
                </div>

                <!-- Lead Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Lead Status
                    </label>
                    <select
                        v-model="filters.status"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
                    >
                        <option value="">All Lead Statuses</option>
                        <option v-for="status in statuses" :key="status.id" :value="status.id">
                            {{ status.name }}
                        </option>
                    </select>
                </div>

                <!-- Call Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Call Status
                    </label>
                    <select
                        v-model="filters.call_status"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
                    >
                        <option value="">All Call Statuses</option>
                        <option v-for="cStatus in call_statuses" :key="cStatus.id" :value="cStatus.name">
                            {{ cStatus.name }}
                        </option>
                    </select>
                </div>

                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        From Date
                    </label>
                    <input
                        type="date"
                        v-model="filters.dateFrom"
                        class="w-full h-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                </div>

                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        To Date
                    </label>
                    <input
                        type="date"
                        v-model="filters.dateTo"
                        class="w-full h-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                </div>

                <!-- User Filter -->
                <div v-if="props.user?.is_admin">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assigned User
                    </label>
                    <select
                        v-model="filters.user"
                        class="w-full h-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">All Users</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">
                            {{ u.name }}
                        </option>
                    </select>
                </div>

                <!-- Logs By Filter -->
                <div v-if="props.user?.is_admin">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logs By
                    </label>
                    <select
                        v-model="filters.log_by"
                        class="w-full h-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">All Users</option>
                        <option v-for="u in users" :key="`log_${u.id}`" :value="u.id">
                            {{ u.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">Active filters:</span>
                    <span v-if="filters.search" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                        Search: "{{ filters.search }}"
                        <button @click="filters.search = ''" class="ml-1 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.service" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Service: {{ services.find(s => s.id == filters.service)?.name }}
                        <button @click="filters.service = ''" class="ml-1 text-blue-600 hover:text-blue-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Lead Status: {{ statuses.find(s => s.id == filters.status)?.name }}
                        <button @click="filters.status = ''" class="ml-1 text-green-600 hover:text-green-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.call_status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                        Call Status: {{ filters.call_status }}
                        <button @click="filters.call_status = ''" class="ml-1 text-indigo-600 hover:text-indigo-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.dateFrom" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        From: {{ new Date(filters.dateFrom).toLocaleDateString() }}
                        <button @click="filters.dateFrom = ''" class="ml-1 text-purple-600 hover:text-purple-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.dateTo" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        To: {{ new Date(filters.dateTo).toLocaleDateString() }}
                        <button @click="filters.dateTo = ''" class="ml-1 text-purple-600 hover:text-purple-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.user && props.user?.is_admin" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        Assigned To: {{ users.find(u => u.id == filters.user)?.name }}
                        <button @click="filters.user = ''" class="ml-1 text-orange-600 hover:text-orange-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.log_by && props.user?.is_admin" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                        Logged By: {{ users.find(u => u.id == filters.log_by)?.name }}
                        <button @click="filters.log_by = ''" class="ml-1 text-teal-600 hover:text-teal-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                </div>
            </div>

            <!-- Quick Stats (when no filters active) -->
            <div v-if="!hasActiveFilters && callLogs.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ Object.keys(filterStats.byService).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Services</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byService).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byService).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ Object.keys(filterStats.byStatus).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Lead Statuses</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byStatus).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byStatus).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ Object.keys(filterStats.byCallStatus).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Call Statuses</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byCallStatus).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byCallStatus).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ Object.keys(filterStats.byUser).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Assigned Users</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byUser).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byUser).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
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
                    <span>{{ hasActiveFilters ? 'Filtered Call Logs' : 'All Call Logs' }}</span>
                </h3>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 p-1 rounded-lg">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-2 rounded-md transition-all',
                                viewMode === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
                            ]"
                            title="Grid View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'p-2 rounded-md transition-all',
                                viewMode === 'table' ? 'bg-white dark:bg-gray-600 shadow-sm text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
                            ]"
                            title="Table View"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>

                    <button 
                        @click="saveDefaultView(viewMode)"
                        class="p-2 text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition-colors"
                        title="Set current view as default"
                    >
                        <svg class="w-5 h-5" :class="{'text-green-500 fill-current': props.user?.default_view_mode === viewMode}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </button>
                    <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full">
                        {{ hasActiveFilters ? `${filteredCallLogs.length} of ${callLogs.length}` : `${callLogs.length} total` }}
                    </span>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div v-if="selectedIds.length > 0" class="mb-6 flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl animate-fade-in-down">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-green-800 dark:text-green-300">
                        {{ selectedIds.length }} items selected
                    </span>
                    <button
                        @click="deleteBulk"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Delete Selected</span>
                    </button>
                </div>
                <button
                    @click="selectedIds = []; selectAll = false"
                    class="text-sm font-medium text-green-600 dark:text-green-400 hover:underline"
                >
                    Deselect All
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="filteredCallLogs.length === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    {{ hasActiveFilters ? 'No call logs match your filters.' : 'No call logs found.' }}
                </p>
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="mt-4 inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Call Log View Wrapper -->
            <div v-else>
                <!-- Grid View -->
                <div v-if="viewMode === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="callLog in filteredCallLogs" :key="callLog.id" class="relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:hover:shadow-white/20 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-white/30 transition-all duration-300 p-4 flex flex-col justify-between">
                        <!-- Selection Checkbox -->
                        <div class="absolute top-4 right-4 z-10">
                            <input
                                type="checkbox"
                                :value="callLog.id"
                                v-model="selectedIds"
                                @change="handleSelect"
                                class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>

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
                            <div class="text-gray-700 dark:text-gray-200 break-words line-clamp-2">
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
                            <button @click="deleteCallLog(callLog)" class="flex-1 bg-red-500 hover:bg-red-600 dark:hover:bg-red-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">Delete</button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div v-else class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            v-model="selectAll"
                                            @change="toggleSelectAll"
                                            class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">Lead Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Lead Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Call Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Logs By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">Dates</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">Actions</th>
                                </tr>
                            </thead>
                             <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="callLog in filteredCallLogs" :key="callLog.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" :class="{ 'bg-green-50/50 dark:bg-green-900/10': selectedIds.includes(callLog.id) }">
                                    <td class="px-6 py-4 ">
                                        <input
                                            type="checkbox"
                                            :value="callLog.id"
                                            v-model="selectedIds"
                                            @change="handleSelect"
                                            class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                                        />
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ callLog.lead?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ callLog.lead?.phone || '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider" :class="getStatusClass(callLog.lead?.status?.name)">
                                            {{ callLog.lead?.status?.name || 'New' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider" :class="getCallStatusClass(callLog)">
                                            {{ getCallStatus(callLog) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ callLog.lead?.service?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ callLog.creator?.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ callLog.created_at ? new Date(callLog.created_at).toLocaleDateString() : '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4   text-xs text-gray-500 dark:text-gray-400">
                                        <div>Call: {{ callLog.call_followup_date ? new Date(callLog.call_followup_date).toLocaleDateString() : '-' }}</div>
                                        <div>Next: {{ callLog.next_call_date ? new Date(callLog.next_call_date).toLocaleDateString() : '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <Link v-if="callLog.lead?.id" :href="route('leads.show', callLog.lead.id)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View Lead</Link>
                                        <button @click="openEditModal(callLog)" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">Edit Call</button>
                                        <button @click="deleteCallLog(callLog)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Call Modal -->
        <EditCallModal
            v-if="showEditModal"
            :callDetail="editingCallDetail"
            :call-statuses="call_statuses"
            @close="closeEditModal"
        />
    </ModernLayout>
</template>
