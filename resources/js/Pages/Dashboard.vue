<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CallModal from '@/Components/CallModal.vue';
import NewLeadModal from '@/Components/NewLeadModal.vue';

const props = defineProps({
    todaysCalls: Array,
    pendingCalls: Array,
    upcomingCalls: Array,
    leads: Array,
    stats: Object,
    users: Array,
    user: Object,
    selectedUser: Object,
    services: Array,
    statuses: Array,
    modalUsers: Array,
});

const showCallModal = ref(false);
const selectedLead = ref(null);
const selectedCallDetail = ref(null);

// New Lead Modal state
const showNewLeadModal = ref(false);

// Pagination state for each section
const todaysCallsPage = ref(1);
const pendingCallsPage = ref(1);
const upcomingCallsPage = ref(1);
const callsPerPage = 5;

const openCallModal = (lead, callDetail = null) => {
    selectedLead.value = lead;
    selectedCallDetail.value = callDetail;
    showCallModal.value = true;
};

const closeCallModal = () => {
    showCallModal.value = false;
    selectedLead.value = null;
    selectedCallDetail.value = null;
};

// New Lead Modal handlers
const openNewLeadModal = () => {
    showNewLeadModal.value = true;
};

const closeNewLeadModal = () => {
    showNewLeadModal.value = false;
};


const filterByUser = (userId) => {
    router.visit(route('dashboard.filter', userId));
};

const clearFilter = () => {
    router.visit(route('dashboard'));
};

// Action button handlers
const scrollToTodaysCalls = () => {
    document.getElementById('todays-calls-section')?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToPendingCalls = () => {
    document.getElementById('pending-calls-section')?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToUpcomingCalls = () => {
    document.getElementById('upcoming-calls-section')?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToFollowups = () => {
    document.getElementById('recent-leads-section')?.scrollIntoView({ behavior: 'smooth' });
};

const openReporting = () => {
    // For now, just show an alert. You can implement actual reporting later
    alert('CRM Reporting feature will be implemented soon!');
};

// Calculate statistics
const totalTodaysCalls = computed(() => props.stats.todays_calls);
const totalPendingCalls = computed(() => props.stats.pending_calls);
const totalUpcomingCalls = computed(() => props.stats.upcoming_calls);
const totalLeads = computed(() => props.stats.total_leads);
const totalCalls = computed(() => props.todaysCalls.length + props.pendingCalls.length + props.upcomingCalls.length);

// Pagination computed properties
const paginatedTodaysCalls = computed(() => {
    const start = (todaysCallsPage.value - 1) * callsPerPage;
    const end = start + callsPerPage;
    return props.todaysCalls.slice(start, end);
});

const paginatedPendingCalls = computed(() => {
    const start = (pendingCallsPage.value - 1) * callsPerPage;
    const end = start + callsPerPage;
    return props.pendingCalls.slice(start, end);
});

const paginatedUpcomingCalls = computed(() => {
    const start = (upcomingCallsPage.value - 1) * callsPerPage;
    const end = start + callsPerPage;
    return props.upcomingCalls.slice(start, end);
});

// Pagination info
const todaysCallsTotalPages = computed(() => Math.ceil(props.todaysCalls.length / callsPerPage));
const pendingCallsTotalPages = computed(() => Math.ceil(props.pendingCalls.length / callsPerPage));
const upcomingCallsTotalPages = computed(() => Math.ceil(props.upcomingCalls.length / callsPerPage));

// Pagination functions
const setTodaysCallsPage = (page) => {
    todaysCallsPage.value = page;
};

const setPendingCallsPage = (page) => {
    pendingCallsPage.value = page;
};

const setUpcomingCallsPage = (page) => {
    upcomingCallsPage.value = page;
};

// Pagination component
const PaginationComponent = {
    props: {
        currentPage: Number,
        totalPages: Number,
        onPageChange: Function
    },
    setup(props) {
        const getPageNumbers = () => {
            const pages = [];
            const maxVisible = 5;

            if (props.totalPages <= maxVisible) {
                for (let i = 1; i <= props.totalPages; i++) {
                    pages.push(i);
                }
            } else {
                const start = Math.max(1, props.currentPage - 2);
                const end = Math.min(props.totalPages, start + maxVisible - 1);

                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
            }

            return pages;
        };

        return { getPageNumbers };
    },
    template: `
        <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 mt-4">
            <button
                v-if="currentPage > 1"
                @click="onPageChange(currentPage - 1)"
                class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
                ←
            </button>

            <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="onPageChange(page)"
                :class="[
                    'px-3 py-1 text-sm rounded-md transition-colors',
                    page === currentPage
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200 hover:bg-gray-300'
                ]"
            >
                {{ page }}
            </button>

            <button
                v-if="currentPage < totalPages"
                @click="onPageChange(currentPage + 1)"
                class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
                →
            </button>
        </div>
    `
};
</script>

<template>
    <Head title="Dashboard" />

    <ModernLayout>
        <!-- Dashboard Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-8 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-3">CRM Dashboard</h1>
                        <p class="text-blue-100 text-lg">Manage your leads and track call activities efficiently</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- New Lead Button -->
                        <button
                            @click="openNewLeadModal"
                            class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>New Lead</span>
                        </button>

                        <div class="hidden md:block">
                            <div class="text-right">
                                <p class="text-blue-100 text-sm">Welcome back,</p>
                                <p class="text-white text-xl font-semibold">{{ user?.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8">
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button @click="scrollToTodaysCalls" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span>Today's Calls</span>
                    </div>
                </button>
                <button @click="scrollToPendingCalls" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Pending Calls</span>
                    </div>
                </button>
                <button @click="scrollToUpcomingCalls" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Upcoming Calls</span>
                    </div>
                </button>
                <button @click="scrollToFollowups" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Recent Leads</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalTodaysCalls }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalPendingCalls }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalUpcomingCalls }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Leads</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalLeads }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin User Filter -->
        <div v-if="(user.is_admin || user.permissions?.users?.includes('view')) && users.length > 0" class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filter by User</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="clearFilter"
                        :class="[
                            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                            !selectedUser ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        All Users
                    </button>
                    <button
                        v-for="user in users"
                        :key="user.id"
                        @click="filterByUser(user.id)"
                        :class="[
                            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                            selectedUser && selectedUser.id === user.id ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        {{ user.name }}
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Today's Calls Section -->
    <div id="todays-calls-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-8 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white">Today's Calls</h3>
                </div>
                <div class="text-white text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full">
                    {{ totalTodaysCalls }} calls
                    <span v-if="todaysCallsTotalPages > 1" class="ml-2">
                        (Page {{ todaysCallsPage }} of {{ todaysCallsTotalPages }})
                    </span>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div v-if="todaysCalls.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No calls scheduled for today.</p>
            </div>
            <div v-else>
                <div class="space-y-4">
                    <div v-for="call in paginatedTodaysCalls" :key="call.id"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ call.lead.name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ call.lead.company_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ call.lead.phone }} • {{ call.lead.email }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ call.call_followup_summary }}</p>
                                <p class="text-xs text-blue-500 mt-1">
                                    Scheduled: {{ new Date(call.next_call_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="openCallModal(call.lead, call)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Call
                                </button>
                                <Link :href="route('leads.show', call.lead.id)"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    View Lead
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination for Today's Calls -->
                <div v-if="todaysCallsTotalPages > 1" class="flex justify-center items-center space-x-2 mt-6">
                    <button
                        v-if="todaysCallsPage > 1"
                        @click="setTodaysCallsPage(todaysCallsPage - 1)"
                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                    >
                        ←
                    </button>

                    <button
                        v-for="page in Array.from({length: todaysCallsTotalPages}, (_, i) => i + 1)"
                        :key="page"
                        @click="setTodaysCallsPage(page)"
                        :class="[
                            'px-3 py-1 text-sm rounded-md transition-colors',
                            page === todaysCallsPage
                                ? 'bg-blue-500 text-white'
                                : 'bg-gray-200 hover:bg-gray-300'
                        ]"
                    >
                        {{ page }}
                    </button>

                    <button
                        v-if="todaysCallsPage < todaysCallsTotalPages"
                        @click="setTodaysCallsPage(todaysCallsPage + 1)"
                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                    >
                        →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Calls Section -->
    <div id="pending-calls-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-8 overflow-hidden">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white">Pending Calls</h3>
                </div>
                <div class="text-white text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full">
                    {{ totalPendingCalls }} overdue
                    <span v-if="pendingCallsTotalPages > 1" class="ml-2">
                        (Page {{ pendingCallsPage }} of {{ pendingCallsTotalPages }})
                    </span>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div v-if="pendingCalls.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No pending calls.</p>
            </div>
            <div v-else>
                <div class="space-y-4">
                    <div v-for="call in paginatedPendingCalls" :key="call.id"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ call.lead.name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ call.lead.company_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ call.lead.phone }} • {{ call.lead.email }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ call.call_followup_summary }}</p>
                                <p class="text-xs text-red-500 mt-1">
                                    Overdue: {{ new Date(call.next_call_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="openCallModal(call.lead, call)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Call
                                </button>
                                <Link :href="route('leads.show', call.lead.id)"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    View Lead
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination for Pending Calls -->
                <div v-if="pendingCallsTotalPages > 1" class="flex justify-center items-center space-x-2 mt-6">
                    <button
                        v-if="pendingCallsPage > 1"
                        @click="setPendingCallsPage(pendingCallsPage - 1)"
                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                    >
                        ←
                    </button>

                    <button
                        v-for="page in Array.from({length: pendingCallsTotalPages}, (_, i) => i + 1)"
                        :key="page"
                        @click="setPendingCallsPage(page)"
                        :class="[
                            'px-3 py-1 text-sm rounded-md transition-colors',
                            page === pendingCallsPage
                                ? 'bg-red-500 text-white'
                                : 'bg-gray-200 hover:bg-gray-300'
                        ]"
                    >
                        {{ page }}
                    </button>

                    <button
                        v-if="pendingCallsPage < pendingCallsTotalPages"
                        @click="setPendingCallsPage(pendingCallsPage + 1)"
                        class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                    >
                        →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Calls Section -->
<div id="upcoming-calls-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-8 overflow-hidden">
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white">Upcoming Calls</h3>
            </div>
            <div class="text-white text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full">
                {{ totalUpcomingCalls }} scheduled
                <span v-if="upcomingCallsTotalPages > 1" class="ml-2">
                    (Page {{ upcomingCallsPage }} of {{ upcomingCallsTotalPages }})
                </span>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div v-if="upcomingCalls.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No upcoming calls scheduled.</p>
        </div>
        <div v-else>
            <div class="space-y-4">
                <div v-for="call in paginatedUpcomingCalls" :key="call.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ call.lead.name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ call.lead.company_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ call.lead.phone }} • {{ call.lead.email }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ call.call_followup_summary }}</p>
                            <p class="text-xs text-green-500 mt-1">
                                Scheduled: {{ new Date(call.next_call_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="openCallModal(call.lead)"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                Schedule
                            </button>
                            <Link :href="route('leads.show', call.lead.id)"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                View Lead
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination for Upcoming Calls -->
            <div v-if="upcomingCallsTotalPages > 1" class="flex justify-center items-center space-x-2 mt-6">
                <button
                    v-if="upcomingCallsPage > 1"
                    @click="setUpcomingCallsPage(upcomingCallsPage - 1)"
                    class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                >
                    ←
                </button>

                <button
                    v-for="page in Array.from({length: upcomingCallsTotalPages}, (_, i) => i + 1)"
                    :key="page"
                    @click="setUpcomingCallsPage(page)"
                    :class="[
                        'px-3 py-1 text-sm rounded-md transition-colors',
                        page === upcomingCallsPage
                            ? 'bg-green-500 text-white'
                            : 'bg-gray-200 hover:bg-gray-300'
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    v-if="upcomingCallsPage < upcomingCallsTotalPages"
                    @click="setUpcomingCallsPage(upcomingCallsPage + 1)"
                    class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                >
                    →
                </button>
            </div>
        </div>
    </div>
</div>


        <!-- Recent Leads Section -->
        <div id="recent-leads-section" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Recent Leads</h3>
                    </div>
                    <div class="text-white text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full">
                        {{ totalLeads }} total
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div v-if="leads.length === 0" class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No leads found.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SL No.</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Leads Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assign To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Next Call Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Options</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(lead, index) in leads.slice(0, 10)" :key="lead.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ lead.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ lead.status.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ lead.phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ lead.service.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ lead.assigned_user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ lead.lead_details && lead.lead_details.length > 0 ? new Date(lead.lead_details[lead.lead_details.length - 1].next_call_date).toLocaleDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-1">
                                        <button
                                            @click="openCallModal(lead)"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs transition-colors"
                                            title="Call"
                                        >
                                            📞
                                        </button>
                                        <Link
                                            :href="route('leads.show', lead.id)"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs transition-colors"
                                            title="View"
                                        >
                                            👁️
                                        </Link>
                                        <Link
                                            :href="route('leads.edit', lead.id)"
                                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded text-xs transition-colors"
                                            title="Edit"
                                        >
                                            ✏️
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Call Modal -->
        <CallModal
            v-if="showCallModal"
            :lead="selectedLead"
            :callDetail="selectedCallDetail"
            @close="closeCallModal"
        />

        <!-- New Lead Modal -->
        <NewLeadModal
            v-if="showNewLeadModal"
            :show="showNewLeadModal"
            :services="services"
            :statuses="statuses"
            :users="modalUsers"
            @close="closeNewLeadModal"
        />
    </ModernLayout>
</template>
