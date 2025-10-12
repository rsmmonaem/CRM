<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CallModal from '@/Components/CallModal.vue';
import NewLeadModal from '@/Components/NewLeadModal.vue';
import EditLeadModal from '@/Components/EditLeadModal.vue';

const props = defineProps({
    leads: Array,
    user: Object,
    services: Array,
    statuses: Array,
    users: Array,
});

const page = usePage();
const showCallModal = ref(false);
const selectedLead = ref(null);
const showEditModal = ref(false);
const editingLead = ref(null);
const showNewLeadModal = ref(false);
const callButtonClicked = ref(new Set());
const viewButtonClicked = ref(new Set());
const editButtonClicked = ref(new Set());

// Filter state
const filters = ref({
    service: '',
    status: '',
    dateFrom: '',
    dateTo: '',
    user: ''
});

const user = computed(() => page.props.auth.user);

const hasPermission = (module, action) => {
    if (user.value?.is_admin) return true;
    return user.value?.permissions?.[module]?.includes(action) || false;
};

// Computed filtered leads
const filteredLeads = computed(() => {
    let result = [...props.leads];

    // Service filter
    if (filters.value.service) {
        result = result.filter(lead => lead.service_id == filters.value.service);
    }

    // Status filter
    if (filters.value.status) {
        result = result.filter(lead => lead.status_id == filters.value.status);
    }

    // Date range filter
    if (filters.value.dateFrom) {
        const fromDate = new Date(filters.value.dateFrom);
        result = result.filter(lead => new Date(lead.created_at) >= fromDate);
    }

    if (filters.value.dateTo) {
        const toDate = new Date(filters.value.dateTo);
        toDate.setHours(23, 59, 59, 999); // Include the entire day
        result = result.filter(lead => new Date(lead.created_at) <= toDate);
    }

    // User filter (admin only)
    if (filters.value.user && user.value?.is_admin) {
        result = result.filter(lead => lead.assigned_user_id == filters.value.user);
    }

    return result;
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return filters.value.service ||
           filters.value.status ||
           filters.value.dateFrom ||
           filters.value.dateTo ||
           filters.value.user;
});

// Clear all filters
const clearFilters = () => {
    filters.value = {
        service: '',
        status: '',
        dateFrom: '',
        dateTo: '',
        user: ''
    };
};

// Get filter statistics with counts
const filterStats = computed(() => {
    const stats = {
        byService: {},
        byStatus: {},
        byUser: {}
    };

    props.leads.forEach(lead => {
        // Service stats
        const serviceName = lead.service?.name || 'Unknown';
        stats.byService[serviceName] = (stats.byService[serviceName] || 0) + 1;

        // Status stats
        const statusName = lead.status?.name || 'Unknown';
        stats.byStatus[statusName] = (stats.byStatus[statusName] || 0) + 1;

        // User stats
        const userName = lead.assigned_user?.name || 'Unassigned';
        stats.byUser[userName] = (stats.byUser[userName] || 0) + 1;
    });

    return stats;
});

// Get count for specific service
const getServiceCount = (serviceId) => {
    if (!serviceId) return props.leads.length;
    return props.leads.filter(lead => lead.service_id == serviceId).length;
};

// Get count for specific status
const getStatusCount = (statusId) => {
    if (!statusId) return props.leads.length;
    return props.leads.filter(lead => lead.status_id == statusId).length;
};

// Get count for specific user
const getUserCount = (userId) => {
    if (!userId) return props.leads.length;
    return props.leads.filter(lead => lead.assigned_user_id == userId).length;
};

const openCallModal = (lead) => {
    selectedLead.value = lead;
    showCallModal.value = true;

    // Show click feedback
    callButtonClicked.value.add(lead.id);
    setTimeout(() => {
        callButtonClicked.value.delete(lead.id);
    }, 3000);
};

const handleViewClick = (leadId) => {
    // Show click feedback
    viewButtonClicked.value.add(leadId);
    setTimeout(() => {
        viewButtonClicked.value.delete(leadId);
    }, 3000);
};


const closeCallModal = () => {
    showCallModal.value = false;
    selectedLead.value = null;
};

const openEditModal = (lead) => {
    editingLead.value = lead;
    showEditModal.value = true;

    // Show click feedback
    editButtonClicked.value.add(lead.id);
    setTimeout(() => {
        editButtonClicked.value.delete(lead.id);
    }, 3000);
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingLead.value = null;
};

const deleteLead = (lead) => {
    if (confirm('Are you sure you want to delete this lead?')) {
        router.delete(route('leads.destroy', lead.id));
    }
};

// New Lead Modal handlers
const openNewLeadModal = () => {
    showNewLeadModal.value = true;
};

const closeNewLeadModal = () => {
    showNewLeadModal.value = false;
};


const getStatusClass = (statusName) => {
    const name = (statusName || '').toLowerCase();

    if (name.includes('won') || name.includes('success')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }

    if (name.includes('lost') || name.includes('cancel')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }

    if (name.includes('contact')) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    }

    if (name.includes('negotiation') || name.includes('deal')) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    }

    if (name.includes('proposal')) {
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    }

    if (name.includes('qualified')) {
        return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
    }

    if (name.includes('new') || name.includes('open')) {
        return 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200';
    }

    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};
</script>

<template>
    <Head title="Leads" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-8 text-white shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Leads Management</h1>
                        <p class="text-blue-100 text-lg">Manage and track your leads efficiently</p>
                    </div>

                    <button
                        v-if="hasPermission('leads', 'create')"
                        @click="openNewLeadModal"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Add New Lead</span>
                    </button>
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
                        {{ filteredLeads.length }} of {{ leads.length }} leads
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <!-- Service Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Service
                    </label>
                    <select
                        v-model="filters.service"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">All Services ({{ leads.length }})</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }} ({{ getServiceCount(service.id) }})
                        </option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status
                    </label>
                    <select
                        v-model="filters.status"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">All Statuses ({{ leads.length }})</option>
                        <option v-for="status in statuses" :key="status.id" :value="status.id">
                            {{ status.name }} ({{ getStatusCount(status.id) }})
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
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    />
                </div>

                <!-- User Filter (Admin Only) -->
                <div v-if="user?.is_admin">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assigned User
                    </label>
                    <select
                        v-model="filters.user"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">All Users ({{ leads.length }})</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ getUserCount(user.id) }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Quick Stats (when no filters active) -->
            <div v-if="!hasActiveFilters && leads.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ Object.keys(filterStats.byService).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Services</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byService).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byService).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ Object.keys(filterStats.byStatus).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Statuses</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byStatus).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byStatus).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ Object.keys(filterStats.byUser).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Users</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Top: {{ Object.entries(filterStats.byUser).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }} ({{ Object.entries(filterStats.byUser).sort((a,b) => b[1] - a[1])[0]?.[1] || 0 }})
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">Active filters:</span>
                    <span v-if="filters.service" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Service: {{ services.find(s => s.id == filters.service)?.name }}
                        <button @click="filters.service = ''" class="ml-1 text-blue-600 hover:text-blue-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                    <span v-if="filters.status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Status: {{ statuses.find(s => s.id == filters.status)?.name }}
                        <button @click="filters.status = ''" class="ml-1 text-green-600 hover:text-green-800">
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
                    <span v-if="filters.user && user?.is_admin" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        User: {{ users.find(u => u.id == filters.user)?.name }}
                        <button @click="filters.user = ''" class="ml-1 text-orange-600 hover:text-orange-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <!-- Leads Section -->
        <div class="dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6 ">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center space-x-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ hasActiveFilters ? 'Filtered Leads' : 'All Leads' }}</span>
                </h3>
                <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                    {{ hasActiveFilters ? `${filteredLeads.length} of ${leads.length}` : `${leads.length} total` }}
                </span>
            </div>

            <!-- Empty State -->
            <div v-if="filteredLeads.length === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    {{ hasActiveFilters ? 'No leads match your filters.' : 'No leads found.' }}
                </p>
                <div class="mt-4 space-x-3">
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                    >
                        Clear Filters
                    </button>
                    <button
                        v-if="leads.length === 0"
                        @click="openNewLeadModal"
                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                    >
                        Create Your First Lead
                    </button>
                </div>
            </div>

            <!-- Lead Cards -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
  <div v-for="lead in filteredLeads" :key="lead.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:hover:shadow-white/20 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-white/30 transition-all duration-300 p-4 flex flex-col justify-between">

    <!-- Header: Name + Status -->
    <div class="flex justify-between items-start mb-2">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-white truncate">
        {{ lead.name || 'Unnamed Lead' }}
      </h4>
      <span class="text-xs font-semibold px-2 py-1 rounded-full" :class="getStatusClass(lead.status.name)">
        {{ lead.status.name }}
      </span>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300 mb-2">
      <div class="truncate"><strong>Phone:</strong> {{ lead.phone || '-' }}</div>
      <div class="truncate"><strong>Email:</strong> {{ lead.email || '-' }}</div>
      <div class="truncate"><strong>Company:</strong> {{ lead.company_name || '-' }}</div>
      <div class="truncate"><strong>Service:</strong> {{ lead.service.name || '-' }}</div>
      <div class="truncate"><strong>Assigned:</strong> {{ lead.assigned_user.name || '-' }}</div>
      <div class="truncate"><strong>Created:</strong> {{ new Date(lead.created_at).toLocaleDateString() }}</div>
    </div>

    <!-- Last Call History (Left-Right) -->
    <div v-if="lead.lead_details.length > 0" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-300">
      <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Last Call</div>
      <div v-for="(call, index) in lead.lead_details.slice(0, 1)" :key="call.id" class="flex justify-between items-start gap-2">
        <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
          {{ new Date(call.created_at).toLocaleDateString() }}
        </div>
        <div class="flex-1 text-gray-700 dark:text-gray-200 truncate">
          {{ call.call_followup_summary || '-' }}
        </div>
      </div>
      <div v-if="lead.lead_details.length > 1" class="text-right text-gray-400 dark:text-gray-500 text-xs">+{{ lead.lead_details.length - 1 }} more</div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-3 flex flex-wrap gap-2">
      <button @click="openCallModal(lead)" class="flex-1 bg-blue-500 hover:bg-blue-600 dark:hover:bg-blue-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Call</button>
      <Link :href="route('leads.show', lead.id)" class="flex-1 bg-green-500 hover:bg-green-600 dark:hover:bg-green-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">View</Link>
      <button @click="openEditModal(lead)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 dark:hover:bg-yellow-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Edit</button>
      <button @click="deleteLead(lead)" class="flex-1 bg-red-500 hover:bg-red-600 dark:hover:bg-red-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Delete</button>
    </div>

  </div>
</div>

        </div>

        <!-- Call Modal -->
        <CallModal
            v-if="showCallModal"
            :lead="selectedLead"
            :statuses="statuses"
            @close="closeCallModal"
        />

        <!-- New Lead Modal -->
        <NewLeadModal
            v-if="showNewLeadModal"
            :show="showNewLeadModal"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeNewLeadModal"
        />

        <!-- Edit Lead Modal -->
        <EditLeadModal
            v-if="showEditModal"
            :lead="editingLead"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeEditModal"
        />
    </ModernLayout>
</template>
