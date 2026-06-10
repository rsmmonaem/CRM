<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
    callResultSummary: { type: Array, default: () => [] },
    leadResultSummary: { type: Array, default: () => [] },
});

const filterUserId = ref(props.filters?.assigned_user_id || '');
const fromDate = ref(props.filters?.from_date || '');
const toDate = ref(props.filters?.to_date || '');

const applyFilters = () => {
    router.get(route('reports.cr-dashboard'), {
        assigned_user_id: filterUserId.value,
        from_date: fromDate.value,
        to_date: toDate.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([filterUserId, fromDate, toDate], () => {
    applyFilters();
});

const clearFilter = () => {
    filterUserId.value = '';
    fromDate.value = '';
    toDate.value = '';
};

const isAdmin = computed(() => props.user?.role === 'admin' || props.user?.is_admin);

// Intotal Assign Lead Done = Intotal Assign Lead - Intotal Assign Lead Pending
const intotalAssignLeadDone = computed(() => {
    return (props.metrics?.intotalAssignLead || 0) - (props.metrics?.intotalPendingCall || 0);
});

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="CR Dashboard Report" />

    <ModernLayout>

        <!-- HEADER -->
        <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6 no-print">
            <div class="space-y-4">
                <Link :href="route('reports.index')" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-700 dark:hover:text-indigo-300 group">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7" />
                    </svg>
                    <span class="ml-2">Return to Reports</span>
                </Link>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                        CR Dashboard Report
                    </h1>
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                        Metrics Summary Report
                    </div>
                </div>
            </div>

            <a :href="route('reports.cr-dashboard.print', filters)" target="_blank" class="flex items-center space-x-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-400 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Professional</span>
            </a>
        </div>

        <!-- FILTER SECTION -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-8 no-print border dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div v-if="isAdmin">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select User</label>
                    <select v-model="filterUserId" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                        <option value="">All Users</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input type="date" v-model="fromDate" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                    <input type="date" v-model="toDate" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                </div>
                <div class="flex items-end">
                    <button @click="clearFilter" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors w-full md:w-auto">
                        Clear Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- METRICS TABLE -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border dark:border-gray-700 overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Lead & Followup Details</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-4 text-left whitespace-nowrap">Intotal Assign Lead</th>
                            <th class="p-4 text-left whitespace-nowrap">Today Assign Lead</th>
                            <th class="p-4 text-left whitespace-nowrap">Intotal Assign Lead Pending</th>
                            <th class="p-4 text-left whitespace-nowrap">Today Assign Lead Pending</th>
                            <th class="p-4 text-left whitespace-nowrap">Intotal Assign Lead Done</th>
                            <th class="p-4 text-left whitespace-nowrap">Today Total Call</th>
                            <th class="p-4 text-left whitespace-nowrap">Intotal Call</th>
                            <th class="p-4 text-left whitespace-nowrap">Repeat Call</th>
                            <th class="p-4 text-left whitespace-nowrap">Today Followup</th>
                            <th class="p-4 text-left whitespace-nowrap">Pending Followup</th>
                            <th class="p-4 text-left whitespace-nowrap">Upcoming Calls</th>
                            <th class="p-4 text-left whitespace-nowrap">Today Visit</th>
                            <th class="p-4 text-left whitespace-nowrap">Total Visit</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 dark:text-white">
                        <tr class="border-t dark:border-gray-700">
                            <td class="p-4 font-bold">{{ metrics?.intotalAssignLead ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.todaytotalAssignLead ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.intotalPendingCall ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.todayPendingCall ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ intotalAssignLeadDone }}</td>
                            <td class="p-4 font-bold">{{ metrics?.todayTotalCall ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.intotalCall ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.repeatCall ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.todayFollowup ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.pendingFollowup ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.upcomingCalls ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.todayVisit ?? 0 }}</td>
                            <td class="p-4 font-bold">{{ metrics?.totalVisit ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- LEAD RESULT SUMMARY -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Lead Result Summary</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" v-if="leadResultSummary?.length > 0">
                        <div v-for="(status, index) in leadResultSummary" :key="index" :class="['card', status.color]">
                            <div class="text-sm font-medium mb-1">{{ status.name }}</div>
                            <div class="text-2xl font-bold">{{ status.count }}</div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No lead statuses found.</div>
                </div>
            </div>

            <!-- CALL RESULT SUMMARY -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Call Result Summary</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" v-if="callResultSummary?.length > 0">
                        <div v-for="(status, index) in callResultSummary" :key="index" :class="['card', status.color]">
                            <div class="text-sm font-medium mb-1">{{ status.name }}</div>
                            <div class="text-2xl font-bold">{{ status.count }}</div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No call statuses found.</div>
                </div>
            </div>
        </div>

    </ModernLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
}

.card{
    padding:15px;
    border-radius:8px;
    color:white;
    text-align:center;
}
.blue{background:linear-gradient(135deg,#396afc,#2948ff);}
.orange{background:linear-gradient(135deg,#f7971e,#ffd200);}
.yellow{background:linear-gradient(135deg,#fceabb,#f8b500); color:black;}
.purple{background:linear-gradient(135deg,#7f7fd5,#86a8e7);}
.green{background:linear-gradient(135deg,#56ab2f,#a8e063);}
.cyan{background:linear-gradient(135deg,#00c6ff,#0072ff);}
.red{background:linear-gradient(135deg,#ff512f,#dd2476);}
.darkgreen{background:linear-gradient(135deg,#11998e,#38ef7d);}
.gray{background:linear-gradient(135deg,#bdc3c7,#2c3e50);}
.pink{background:linear-gradient(135deg,#ff758c,#ff7eb3);}
</style>