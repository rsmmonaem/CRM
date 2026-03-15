<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    results: Array,
    users: Array,
    statuses: Array,
    filters: Object,
});

// Calculate grand total or other metrics if needed
const getTotal = (userId, statusName) => {
    const item = props.results.find(r => r.assigned_user_id == userId && r.call_status === statusName);
    return item ? item.total : 0;
};

const getUserTotal = (userId) => {
    return props.results
        .filter(r => r.assigned_user_id == userId)
        .reduce((sum, current) => sum + current.total, 0);
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="User Summary Report" />

    <ModernLayout>
        <div class="mb-8 flex justify-between items-end">
            <div>
                <Link :href="route('reports.index')" class="text-blue-500 hover:text-blue-700 font-bold mb-4 flex items-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Center
                </Link>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">User Performance Summary</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Aggregated counts of Calls by user and their current status.</p>
            </div>
            
            <a :href="route('reports.user-summary.print', filters)" target="_blank" class="flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Matrix</span>
            </a>
        </div>

        <!-- Matrix View -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest min-w-[200px]">User Name</th>
                            <th v-for="status in statuses" :key="status.id" class="px-6 py-5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest bg-indigo-50/30 dark:bg-indigo-900/10 min-w-[120px]">
                                {{ status.name }}
                            </th>
                            <th class="px-8 py-5 text-center text-xs font-bold text-gray-800 dark:text-white uppercase tracking-widest bg-gray-100/50 dark:bg-gray-800/50 min-w-[100px]">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white border-r dark:border-gray-700">
                                {{ user.name }}
                            </td>
                            <td v-for="status in statuses" :key="status.id" class="px-6 py-6 whitespace-nowrap text-center">
                                <span v-if="getTotal(user.id, status.name) > 0" class="inline-flex items-center justify-center min-w-[32px] h-[32px] rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold text-sm">
                                    {{ getTotal(user.id, status.name) }}
                                </span>
                                <span v-else class="text-gray-300 dark:text-gray-600 font-medium">-</span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center bg-gray-50/50 dark:bg-gray-900/30 border-l dark:border-gray-700">
                                <span class="text-md font-extrabold text-indigo-600 dark:text-indigo-400">
                                    {{ getUserTotal(user.id) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td :colspan="statuses.length + 2" class="px-8 py-16 text-center text-gray-500 italic">No users available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="px-8 py-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-sm">
                 <div class="text-gray-500 dark:text-gray-400">
                     <span class="font-bold">Summary Period:</span> 
                     {{ filters.from_date ? new Date(filters.from_date).toLocaleDateString() : 'Start' }} - {{ filters.to_date ? new Date(filters.to_date).toLocaleDateString() : 'Today' }}
                 </div>
                 <div class="text-indigo-600 dark:text-indigo-400 font-extrabold text-lg">
                     Grand Total: {{ results.reduce((sum, r) => sum + r.total, 0) }} Call
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
</style>
