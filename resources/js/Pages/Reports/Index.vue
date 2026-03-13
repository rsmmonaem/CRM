<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    services: Array,
    statuses: Array,
    users: Array,
});

const user = computed(() => usePage().props.auth.user);

// Unified form for all reports
const form = useForm({
    service_id: '',
    status_id: '',
    assigned_user_id: '',
    from_date: '',
    to_date: '',
});

const generateLeadReport = () => {
    form.get(route('reports.leads'));
};

const generateCallReport = () => {
    form.get(route('reports.call-logs'));
};

const generateUserSummary = () => {
    form.get(route('reports.user-summary'));
};
</script>

<template>
    <Head title="CRM Reports" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl p-8 text-white shadow-xl flex justify-between items-center transition-all duration-300">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-3">Reports Center</h1>
                    <p class="text-blue-100 text-lg opacity-90">Analysis and information at your fingertips</p>
                </div>
                <div class="hidden md:block">
                    <div class="p-4 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrated Filter Hub -->
        <div class="mb-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-5 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center space-x-3">
                    <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                        </svg>
                    </span>
                    <span>Report Filters</span>
                </h2>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Service Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Service (Project) Wise</label>
                        <select v-model="form.service_id" class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">All Services</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                        </select>
                    </div>

                    <!-- Status Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Lead Status Wise</label>
                        <select v-model="form.status_id" class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">All Statuses</option>
                            <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                        </select>
                    </div>

                    <!-- User Selector -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">User Assigned Wise</label>
                        <select v-model="form.assigned_user_id" class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">All Users</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">From & To Date</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" v-model="form.from_date" class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                            <input type="date" v-model="form.to_date" class="w-full h-12 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <!-- Lead Report Button -->
            <button 
                v-if="user.is_admin || user.permissions?.['report_leads']?.includes('view')"
                @click="generateLeadReport" 
                class="group relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 text-left hover:shadow-2xl hover:border-blue-500/50 transition-all duration-300 overflow-hidden active:scale-95"
            >
                <div class="absolute top-0 right-0 w-32 h-32 -mr-16 -mt-16 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
                <div class="mb-5 inline-flex p-4 bg-blue-100 dark:bg-blue-900/40 rounded-2xl text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2 underline sm:no-underline underline-offset-4 decoration-blue-500/30">Lead Report</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Detailed list of leads matching your specified criteria and date ranges.</p>
                <div class="mt-6 flex items-center text-blue-600 dark:text-blue-400 font-bold group-hover:translate-x-2 transition-transform">
                    <span>Generate Now</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </button>

            <!-- Call Log Report Button -->
            <button 
                v-if="user.is_admin || user.permissions?.['report_call_logs']?.includes('view')"
                @click="generateCallReport" 
                class="group relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 text-left hover:shadow-2xl hover:border-orange-500/50 transition-all duration-300 overflow-hidden active:scale-95"
            >
                <div class="absolute top-0 right-0 w-32 h-32 -mr-16 -mt-16 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all"></div>
                <div class="mb-5 inline-flex p-4 bg-orange-100 dark:bg-orange-900/40 rounded-2xl text-orange-600 dark:text-orange-400 group-hover:bg-orange-600 group-hover:text-white transition-all duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2 underline sm:no-underline underline-offset-4 decoration-orange-500/30">Call Log Report</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Review interaction history and follow-up activities in a detailed format.</p>
                <div class="mt-6 flex items-center text-orange-600 dark:text-orange-400 font-bold group-hover:translate-x-2 transition-transform">
                    <span>View History</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </button>

            <!-- User Summary Report Button -->
            <button 
                v-if="user.is_admin || user.permissions?.['report_user_summary']?.includes('view')"
                @click="generateUserSummary" 
                class="group relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 text-left hover:shadow-2xl hover:border-emerald-500/50 transition-all duration-300 overflow-hidden active:scale-95"
            >
                <div class="absolute top-0 right-0 w-32 h-32 -mr-16 -mt-16 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="mb-5 inline-flex p-4 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2 underline sm:no-underline underline-offset-4 decoration-emerald-500/30">Performance Summary</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Aggregated totals of leads grouping by user and current lead status.</p>
                <div class="mt-6 flex items-center text-emerald-600 dark:text-emerald-400 font-bold group-hover:translate-x-2 transition-transform">
                    <span>View Totals</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </button>
        </div>
    </ModernLayout>
</template>
