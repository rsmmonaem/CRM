<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// Simple debounce function
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}
import CallModal from '@/Components/CallModal.vue';
import NewLeadModal from '@/Components/NewLeadModal.vue';
import EditLeadModal from '@/Components/EditLeadModal.vue';
import LeadShowModal from '@/Components/LeadShowModal.vue';
import ImportLeadModal from '@/Components/ImportLeadModal.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    leads: Object,
    user: Object,
    services: Array,
    statuses: Array,
    call_statuses: Array,
    users: Array,
    filters: Object,
    stats: Object,
});

const page = usePage();
const showCallModal = ref(false);
const selectedLead = ref(null);
const showEditModal = ref(false);
const editingLead = ref(null);
const showNewLeadModal = ref(false);
const showImportModal = ref(false);
const showLeadModal = ref(false);
const selectedLeadForShow = ref(null);
const callButtonClicked = ref(new Set());
const viewButtonClicked = ref(new Set());
const editButtonClicked = ref(new Set());

// Selection state
const selectedIds = ref([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = filteredLeads.value.map(lead => lead.id);
    } else {
        selectedIds.value = [];
    }
};

const handleSelect = () => {
    if (selectedIds.value.length === filteredLeads.value.length) {
        selectAll.value = true;
    } else {
        selectAll.value = false;
    }
};

const deleteBulk = () => {
    if (selectedIds.value.length === 0) return;

    Swal.fire({
        title: 'Bulk Delete Leads',
        text: `Are you sure you want to delete ${selectedIds.value.length} selected leads? This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete all',
        cancelButtonText: 'Cancel',
        background: page.props.auth.user?.dark_mode ? '#1F2937' : '#FFFFFF',
        color: page.props.auth.user?.dark_mode ? '#FFFFFF' : '#111827',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('leads.bulk-destroy'), {
                ids: selectedIds.value
            }, {
                onSuccess: () => {
                    selectedIds.value = [];
                    selectAll.value = false;
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Selected leads have been removed.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};

// Filter state
const filters = ref({
    search: props.filters?.search || '',
    service: props.filters?.service || '',
    status: props.filters?.status || '',
    dateFrom: props.filters?.dateFrom || '',
    dateTo: props.filters?.dateTo || '',
    user: props.filters?.user || ''
});

const user = computed(() => page.props.auth.user);

const hasPermission = (module, action) => {
    if (user.value?.is_admin) return true;
    return user.value?.permissions?.[module]?.includes(action) || false;
};

// Watch for filter changes and reload via Inertia
watch(filters, debounce((newFilters) => {
    // Only send non-empty values
    const query = {};
    for (const key in newFilters) {
        if (newFilters[key]) {
            query[key] = newFilters[key];
        }
    }
    
    router.get(route('leads.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 500), { deep: true });

// Computed filtered leads are now just the paginated items
const filteredLeads = computed(() => {
    return props.leads.data || [];
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return filters.value.search ||
           filters.value.service ||
           filters.value.status ||
           filters.value.dateFrom ||
           filters.value.dateTo ||
           filters.value.user;
});

// Clear all filters
const clearFilters = () => {
    filters.value = {
        search: '',
        service: '',
        status: '',
        dateFrom: '',
        dateTo: '',
        user: ''
    };
};

// Get filter statistics with counts from backend
const filterStats = computed(() => {
    return {
        byService: props.stats?.services || {},
        byStatus: props.stats?.statuses || {},
        byUser: props.stats?.users || {}
    };
});

// Get count for specific service
const getServiceCount = (serviceId) => {
    if (!serviceId) return props.stats?.total || 0;
    return props.stats?.services?.[serviceId] || 0;
};

// Get count for specific status
const getStatusCount = (statusId) => {
    if (!statusId) return props.stats?.total || 0;
    return props.stats?.statuses?.[statusId] || 0;
};

// Get count for specific user
const getUserCount = (userId) => {
    if (!userId) return props.stats?.total || 0;
    return props.stats?.users?.[userId] || 0;
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
    Swal.fire({
        title: 'Delete Lead',
        text: `Are you sure you want to delete "${lead.name || 'this lead'}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        background: page.props.auth.user?.dark_mode ? '#1F2937' : '#FFFFFF',
        color: page.props.auth.user?.dark_mode ? '#FFFFFF' : '#111827',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('leads.destroy', lead.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Lead has been deleted.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};

// New Lead Modal handlers
const openNewLeadModal = () => {
    showNewLeadModal.value = true;
};

const closeNewLeadModal = () => {
    showNewLeadModal.value = false;
};

const openLeadModal = (lead) => {
    selectedLeadForShow.value = lead;
    showLeadModal.value = true;
};

const closeLeadModal = () => {
    showLeadModal.value = false;
    selectedLeadForShow.value = null;
};

const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
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

const viewMode = ref(props.user?.default_view_mode || 'grid'); // Initialize from DB

const saveDefaultView = (mode) => {
    router.patch(route('profile.preference.update'), {
        default_view_mode: mode
    });
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

                    <button
                        v-if="hasPermission('leads', 'create')"
                        @click="openImportModal"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <span>Import Leads</span>
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
                        {{ leads.total }} leads found
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Search Filter -->
                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Search Leads
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
                            placeholder="Search name, phone, email..."
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
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
                    >
                        <option value="">All Statuses</option>
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
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
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
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm h-10"
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
                        <option value="">All Users</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ getUserCount(user.id) }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Quick Stats (when no filters active) -->
            <div v-if="!hasActiveFilters && leads.data.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ Object.keys(filterStats.byService).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Services</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ Object.keys(filterStats.byStatus).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Statuses</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ Object.keys(filterStats.byUser).length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Users</div>
                    </div>
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
                <div class="flex items-center space-x-3">
                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 p-1 rounded-lg">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-2 rounded-md transition-all',
                                viewMode === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
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
                                viewMode === 'table' ? 'bg-white dark:bg-gray-600 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
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
                        class="p-2 text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors"
                        title="Set current view as default"
                    >
                        <svg class="w-5 h-5" :class="{'text-blue-500 fill-current': props.user?.default_view_mode === viewMode}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </button>
                    <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                        {{ hasActiveFilters ? `${leads.total} match filter` : `${leads.total} total` }}
                    </span>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div v-if="selectedIds.length > 0" class="mb-6 flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl animate-fade-in-down">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-blue-800 dark:text-blue-300">
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
                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
                >
                    Deselect All
                </button>
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
                        v-if="!hasActiveFilters && leads.data.length === 0"
                        @click="openNewLeadModal"
                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                    >
                        Create Your First Lead
                    </button>
                </div>
            </div>

            <!-- Lead View Wrapper -->
            <div v-if="filteredLeads.length > 0">
                <!-- Grid View -->
                <div v-if="viewMode === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="lead in filteredLeads" :key="lead.id" class="relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:hover:shadow-white/20 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-white/30 transition-all duration-300 p-4 flex flex-col justify-between">
                        <!-- Selection Checkbox -->
                        <div class="absolute top-4 right-4 z-10">
                            <input
                                type="checkbox"
                                :value="lead.id"
                                v-model="selectedIds"
                                @change="handleSelect"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                            />
                        </div>

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

                        <!-- Last Call History -->
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
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button @click="openCallModal(lead)" class="flex-1 bg-blue-500 hover:bg-blue-600 dark:hover:bg-blue-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Call</button>
                            <button @click="openLeadModal(lead)" class="flex-1 bg-green-500 hover:bg-green-600 dark:hover:bg-green-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">View</button>
                            <button @click="openEditModal(lead)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 dark:hover:bg-yellow-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Edit</button>
                            <button @click="deleteLead(lead)" class="flex-1 bg-red-500 hover:bg-red-600 dark:hover:bg-red-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Delete</button>
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
                                             class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                                         />
                                     </th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[140px]">Lead</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[140px]">Contact</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Service</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Status</th>
                                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Assigned</th>
                                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="lead in filteredLeads" :key="lead.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-50/50 dark:bg-blue-900/10': selectedIds.includes(lead.id) }">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            :value="lead.id"
                                            v-model="selectedIds"
                                            @change="handleSelect"
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 bg-white dark:bg-gray-700 dark:border-gray-600"
                                        />
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ lead.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ lead.company_name || 'No Company' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ lead.phone }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ lead.email }}</div>
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ lead.service.name }}</span>
                                    </td>
                                    <td class="px-6 py-4  ">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider" :class="getStatusClass(lead.status.name)">
                                            {{ lead.status.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4   text-sm text-gray-500 dark:text-gray-400">
                                        {{ lead.assigned_user.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button @click="openCallModal(lead)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Call</button>
                                        <button @click="openLeadModal(lead)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">View</button>
                                        <button @click="openEditModal(lead)" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">Edit</button>
                                        <button @click="deleteLead(lead)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination Details -->
                <div v-if="leads.links && leads.links.length > 3" class="mt-6 flex items-center justify-between">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ leads.from }}</span>
                                to
                                <span class="font-medium">{{ leads.to }}</span>
                                of
                                <span class="font-medium">{{ leads.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, key) in leads.links" :key="key">
                                    <Link v-if="link.url" :href="link.url"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                        :class="[
                                            link.active 
                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 dark:bg-indigo-900 dark:border-indigo-500 dark:text-indigo-200' 
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                            key === 0 ? 'rounded-l-md' : '',
                                            key === leads.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                        preserve-scroll
                                    />
                                    <span v-else 
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-500 cursor-not-allowed"
                                        :class="[
                                            key === 0 ? 'rounded-l-md' : '',
                                            key === leads.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                    >
                                    </span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Call Modal -->
        <CallModal
            v-if="showCallModal"
            :lead="selectedLead"
            :statuses="statuses"
            :call-statuses="call_statuses"
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

        <!-- Lead Show Modal -->
        <LeadShowModal
            v-if="showLeadModal"
            :show="showLeadModal"
            :lead="selectedLeadForShow"
            :services="services"
            :statuses="statuses"
            :call-statuses="call_statuses"
            :users="users"
            :user="user"
            @close="closeLeadModal"
        />

        <!-- Import Lead Modal -->
        <ImportLeadModal
            v-if="showImportModal"
            :show="showImportModal"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeImportModal"
        />
    </ModernLayout>
</template>
