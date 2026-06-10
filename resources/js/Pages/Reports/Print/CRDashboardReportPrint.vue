<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    metrics: Object,
    callResultSummary: Array,
    leadResultSummary: Array,
    filters: Object,
    printTime: String,
});
</script>

<template>
    <Head title="CR Dashboard Report - Print" />
    
    <div class="printable-container">
        <!-- External Bootstrap for Printing -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        
        <div class="btn-group no-print">
            <button onclick="window.print()" class="btn btn-primary">Print Now</button>
            <button onclick="window.close()" class="btn btn-secondary ml-2">Close Window</button>
        </div>

        <div style="width: 95%; margin: auto;">
            <div class="row m-t-30 m-b-30">
                <div class="col-12 text-center company-name pb-3">
                    <h2 class="font-weight-bold">CR DASHBOARD REPORT</h2>
                    <p class="m-0">Generating strategic insights and data summaries</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <p style="font-size: 14px" class="m-0"><strong>Printing Date & Time:</strong> {{ printTime }}</p>
                </div>
                <div class="col-6 text-right">
                    <p v-if="filters?.from_date" style="font-size: 14px" class="m-0"><strong>From:</strong> {{ filters.from_date }}</p>
                    <p v-if="filters?.to_date" style="font-size: 14px" class="m-0"><strong>To:</strong> {{ filters.to_date }}</p>
                </div>
            </div>

            <h4 class="mb-3 mt-4">Lead & Followup Details</h4>
            <table class="table table-bordered table-striped">
                <thead class="thead-light text-center">
                    <tr>
                        <th style="font-size: 12px">Total Assign Lead</th>
                        <th style="font-size: 12px">Today Assign Lead</th>
                        <th style="font-size: 12px">Total Assign Pending</th>
                        <th style="font-size: 12px">Today Assign Pending</th>
                        <th style="font-size: 12px">Total Assign Done</th>
                        <th style="font-size: 12px">Today Call</th>
                        <th style="font-size: 12px">Total Call</th>
                        <th style="font-size: 12px">Repeat Call</th>
                        <th style="font-size: 12px">Today Followup</th>
                        <th style="font-size: 12px">Pending Followup</th>
                        <th style="font-size: 12px">Upcoming Calls</th>
                        <th style="font-size: 12px">Today Visit</th>
                        <th style="font-size: 12px">Total Visit</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td>{{ metrics?.intotalAssignLead ?? 0 }}</td>
                        <td>{{ metrics?.todaytotalAssignLead ?? 0 }}</td>
                        <td>{{ metrics?.intotalPendingCall ?? 0 }}</td>
                        <td>{{ metrics?.todayPendingCall ?? 0 }}</td>
                        <td>{{ (metrics?.intotalAssignLead || 0) - (metrics?.intotalPendingCall || 0) }}</td>
                        <td>{{ metrics?.todayTotalCall ?? 0 }}</td>
                        <td>{{ metrics?.intotalCall ?? 0 }}</td>
                        <td>{{ metrics?.repeatCall ?? 0 }}</td>
                        <td>{{ metrics?.todayFollowup ?? 0 }}</td>
                        <td>{{ metrics?.pendingFollowup ?? 0 }}</td>
                        <td>{{ metrics?.upcomingCalls ?? 0 }}</td>
                        <td>{{ metrics?.todayVisit ?? 0 }}</td>
                        <td>{{ metrics?.totalVisit ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-6">
                    <h5 class="mb-3">Lead Result Summary</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Status Name</th>
                                <th class="text-center">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(status, index) in leadResultSummary" :key="'l'+index">
                                <td>{{ status.name }}</td>
                                <td class="text-center font-weight-bold">{{ status.count }}</td>
                            </tr>
                            <tr v-if="!leadResultSummary || leadResultSummary.length === 0">
                                <td colspan="2" class="text-center text-muted">No statuses found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <h5 class="mb-3">Call Result Summary</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Status Name</th>
                                <th class="text-center">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(status, index) in callResultSummary" :key="'c'+index">
                                <td>{{ status.name }}</td>
                                <td class="text-center font-weight-bold">{{ status.count }}</td>
                            </tr>
                            <tr v-if="!callResultSummary || callResultSummary.length === 0">
                                <td colspan="2" class="text-center text-muted">No statuses found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-5 pt-4">
                <div class="col-4 border-top text-center pt-2">
                    <p class="small">Generated By CRM</p>
                </div>
                <div class="col-4"></div>
                <div class="col-4 border-top text-center pt-2">
                    <p class="small">Authorized Signature</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Global styles for this printable component only */
@media screen {
    body { background-color: #f4f4f4; padding: 20px 0; }
    .printable-container { background-color: white; padding-bottom: 50px; }
}

@media print {
    body { background-color: white; padding: 0; }
    .no-print { display: none !important; }
    .table-bordered th, .table-bordered td { border: 1px solid rgba(0,0,0,.3) !important; }
}

.table td { vertical-align: middle; }
.m-b-30{ margin-bottom: 30px; }
.m-t-30{ margin-top: 30px; }
.company-name{ border-bottom: 1px solid rgba(0,0,0,.3); }
.btn-group { width: 95%; margin: 20px auto; text-align: right; }
</style>
