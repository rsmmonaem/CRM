<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    leads: Array,
    filters: Object,
});

const getStatusClass = (statusName) => {
    const name = (statusName || '').toLowerCase();
    if (name.includes('won') || name.includes('success')) return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
    if (name.includes('lost') || name.includes('cancel')) return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300';
};
</script>

<template>
    <Head title="Lead Report View" />

    <ModernLayout>
        <!-- Report Header -->
        <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6 no-print">
            <div class="space-y-4">
                <Link :href="route('reports.index')" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-700 dark:hover:text-indigo-300 group">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7" />
                    </svg>
                    <span class="ml-2">Return to Reports</span>
                </Link>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Lead Data Analysis</h1>
                    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 font-medium">
                        <span class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full text-xs font-bold border dark:border-gray-700">{{ leads.length }} records found</span>
                        <span v-if="filters.from_date" class="text-xs">• From: {{ filters.from_date }}</span>
                        <span v-if="filters.to_date" class="text-xs">• To: {{ filters.to_date }}</span>
                    </div>
                </div>
            </div>
            
            <a :href="route('reports.leads.print', filters)" target="_blank" class="flex items-center space-x-2 bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-400 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Professional</span>
            </a>
        </div>

        <!-- Data Visualization Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[120px]">Generated At</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-800 dark:text-white uppercase tracking-widest whitespace-nowrap min-w-[150px]">Lead Name</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[150px]">Contact Info</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[150px]">Assigned Service</th>
                            <th class="px-8 py-5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[100px]">Status</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[130px]">Owned By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <tr v-for="lead in leads" :key="lead.id" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ new Date(lead.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-sm font-extrabold text-gray-900 dark:text-white">
                                    {{ lead.name || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ lead.phone }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ lead.email }}</div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                                    {{ lead.service?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <span class="px-3 py-1.5 text-[10px] font-extrabold rounded-lg uppercase tracking-wider shadow-sm" :class="getStatusClass(lead.status?.name)">
                                    {{ lead.status?.name }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-[10px] uppercase font-bold text-gray-600 dark:text-gray-300">
                                        {{ lead.assigned_user?.name?.charAt(0) || '?' }}
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ lead.assigned_user?.name || 'Unassigned' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="leads.length === 0">
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-500 font-bold italic">No records found for the selected criteria.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ModernLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
    .bg-white, .dark\:bg-gray-800 {
        background-color: white !important;
        color: black !important;
    }
}
</style>
