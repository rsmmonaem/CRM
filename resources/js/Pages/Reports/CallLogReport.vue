<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    callLogs: Array,
    filters: Object,
});
</script>

<template>
    <Head title="Call Interaction Reports" />

    <ModernLayout>
        <!-- Report Header -->
        <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6 no-print">
            <div class="space-y-4">
                <Link :href="route('reports.index')" class="inline-flex items-center text-orange-600 dark:text-orange-400 font-bold hover:text-orange-700 dark:hover:text-orange-300 group">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7" />
                    </svg>
                    <span class="ml-2">Return to Center</span>
                </Link>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Call Interaction Reports</h1>
                    <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 font-medium">
                        <span class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full text-xs font-bold border dark:border-gray-700">{{ callLogs.length }} interactions tracked</span>
                        <span v-if="filters.from_date" class="text-xs font-bold">• Period: {{ filters.from_date }} - {{ filters.to_date || 'Today' }}</span>
                    </div>
                </div>
            </div>
            
            <a :href="route('reports.call-logs.print', filters)" target="_blank" class="flex items-center space-x-2 bg-orange-600 dark:bg-orange-500 hover:bg-orange-700 dark:hover:bg-orange-400 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Full Audit Print</span>
            </a>
        </div>

        <!-- Call Log Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[150px]">Timestamp</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-800 dark:text-white uppercase tracking-widest whitespace-nowrap min-w-[180px]">Lead Information</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[130px]">Agent / Member</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[150px]">Interaction Status</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[300px]">Follow-up Outcome</th>
                            <th class="px-8 py-5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap min-w-[120px]">Scheduled Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <tr v-for="log in callLogs" :key="log.id" class="hover:bg-orange-50/20 dark:hover:bg-orange-900/10 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                    {{ new Date(log.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) }}
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="text-sm font-extrabold text-gray-900 dark:text-white">{{ log.lead?.name || 'Unknown' }}</div>
                                <div class="flex items-center mt-1 space-x-2">
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                        {{ log.lead?.status?.name || '---' }}
                                    </span>
                                    <div class="text-xs font-semibold text-gray-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ log.lead?.phone }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-slate-200 dark:bg-slate-700 rounded-full flex items-center justify-center text-[10px] uppercase font-extrabold text-slate-600 dark:text-slate-300">
                                        {{ log.creator?.name?.charAt(0) || '?' }}
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ log.creator?.name || 'Staff' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="px-3 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border dark:border-gray-700">
                                    {{ log.call_status || (log.called_at ? 'Completed' : 'Scheduled') }}
                                </span>
                            </td>
                            <td class="px-8 py-6 max-w-md">
                                <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed italic" :title="log.call_followup_summary">
                                    {{ log.call_followup_summary || 'No summary recorded' }}
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <div v-if="log.next_call_date" class="inline-flex flex-col items-center">
                                    <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1.5 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                        {{ new Date(log.next_call_date).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) }}
                                    </span>
                                </div>
                                <span v-else class="text-xs text-gray-300 dark:text-gray-600">N/A</span>
                            </td>
                        </tr>
                        <tr v-if="callLogs.length === 0">
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-500 font-bold italic tracking-wide">No activity logs recorded for the selected range.</span>
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
}
</style>
