<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    callLogs: Array,
    filters: Object,
    printTime: String,
});
</script>

<template>
    <Head title="Call Log History - Print" />
    
    <div class="printable-container">
        <!-- External Bootstrap for Printing -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        
        <div class="btn-group no-print">
            <button onclick="window.print()" class="btn btn-warning font-weight-bold">Print Statistics</button>
            <button onclick="window.close()" class="btn btn-secondary ml-2">Close</button>
        </div>

        <div style="width: 95%; margin: auto;">
            <div class="row m-t-30 m-b-30">
                <div class="col-12 text-center company-name pb-3">
                    <h2 class="font-weight-bold text-uppercase">Call Interaction History</h2>
                    <p class="m-0 italic">Detailing client engagement and performance trends</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <p style="font-size: 14px" class="m-0 font-weight-bold">Status Update: {{ printTime }}</p>
                </div>
            </div>

            <table class="table table-bordered table-sm">
                <thead class="thead-dark text-center">
                    <tr>
                        <th width="12%">Log Time</th>
                        <th width="18%">Lead Name</th>
                        <th width="10%">Lead Status</th>
                        <th width="10%">Inter. Status</th>
                        <th width="12%">Handled By</th>
                        <th>Follow-up Summary</th>
                        <th width="10%">Next Call Date</th>
                    </tr>
                </thead>
                <tbody class="text-left">
                    <tr v-for="log in callLogs" :key="log.id">
                        <td class="text-center font-weight-bold small">{{ new Date(log.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) }}</td>
                        <td>
                            <div class="font-weight-bold text-primary">{{ log.lead?.name || '---' }}</div>
                            <div class="small">
                                <span class="font-weight-bold">{{ log.lead?.phone }}</span>
                                <span v-if="log.lead?.service?.name" class="ml-1 text-muted">• {{ log.lead?.service?.name }}</span>
                            </div>
                            <div class="small italic text-muted" style="font-size: 10px">
                                Lead since: {{ log.lead?.created_at ? new Date(log.lead.created_at).toLocaleDateString() : '---' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge small text-uppercase"
                                :class="{
                                    'badge-success': log.lead?.status?.name?.toLowerCase().includes('won'),
                                    'badge-danger': log.lead?.status?.name?.toLowerCase().includes('lost'),
                                    'badge-info': log.lead?.status?.name && !log.lead?.status?.name?.toLowerCase().includes('won') && !log.lead?.status?.name?.toLowerCase().includes('lost'),
                                    'badge-light border': !log.lead?.status?.name
                                }"
                            >
                                {{ log.lead?.status?.name || '---' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning small text-uppercase text-white" style="background-color: #f39c12">{{ log.call_status || 'Handled' }}</span>
                        </td>
                        <td class="small">{{ log.creator?.name }}</td>
                        <td class="small italic">{{ log.call_followup_summary || 'No Interaction Details' }}</td>
                        <td class="text-center small font-weight-bold">{{ log.next_call_date ? new Date(log.next_call_date).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : 'N/A' }}</td>
                    </tr>
                    <tr v-if="callLogs.length === 0">
                        <td colspan="7" class="py-5 text-center font-italic">Zero interaction logs recorded for this metadata criteria.</td>
                    </tr>
                </tbody>
            </table>

            <div class="row m-t-30">
                <div class="col-4 border-top text-center pt-2">
                    <p class="small">Report Compiled By CRM</p>
                </div>
                <div class="col-4"></div>
                <div class="col-4 border-top text-center pt-2 font-weight-bold">
                    <p class="small text-uppercase">Management Approval</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Global styles for this printable component only */
@media screen {
    body { background-color: #f7f9fc; padding: 20px 0; }
    .printable-container { background-color: white; border-radius: 4px; box-shadow: 0 0 10px rgba(0,0,0,.05); }
}

@media print {
    body { background-color: white; padding: 0; }
    .no-print { display: none !important; }
    .table-bordered th, .table-bordered td { border: 1px solid rgba(0,0,0,.3) !important; }
    .table-sm th, .table-sm td { padding: 4px !important; }
}

.table td { vertical-align: middle; }
.m-b-30{ margin-bottom: 30px; }
.m-t-30{ margin-top: 30px; }
.company-name{ border-bottom: 1px solid rgba(0,0,0,.5); }
.btn-group { width: 95%; margin: 20px auto; text-align: right; }
</style>
