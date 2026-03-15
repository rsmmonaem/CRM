<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    results: Array,
    users: Array,
    statuses: Array,
    filters: Object,
    printTime: String,
});

const getTotal = (userId, statusName) => {
    const item = props.results.find(r => r.assigned_user_id == userId && r.call_status === statusName);
    return item ? item.total : 0;
};

const getUserTotal = (userId) => {
    return props.results
        .filter(r => r.assigned_user_id == userId)
        .reduce((sum, current) => sum + current.total, 0);
};
</script>

<template>
    <Head title="User Performance Matrix - Print" />
    
    <div class="printable-container">
        <!-- External Bootstrap for Printing -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        
        <div class="btn-group no-print">
            <button onclick="window.print()" class="btn btn-success font-weight-bold">Download Analysis</button>
            <button onclick="window.close()" class="btn btn-secondary ml-2">Exit</button>
        </div>

        <div style="width: 95%; margin: auto;">
            <div class="row m-t-30 m-b-30">
                <div class="col-12 text-center company-name pb-3">
                    <h2 class="font-weight-bold">USER PERFORMANCE SUMMARY</h2>
                    <p class="m-0 uppercase">Team Lead/Managerial Statistics Matrix</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <p style="font-size: 15px" class="m-0  pl-3"><strong>Printing Date & Time:</strong> {{ printTime }}</p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="bg-gray text-center">
                    <tr>
                        <th class="vertical-center">Staff Name</th>
                        <th v-for="status in statuses" :key="status.id" class="text-xs uppercase bg-light">
                            {{ status.name }}
                        </th>
                        <th class="bg-dark text-white font-weight-bold">GRAND TOTAL</th>
                    </tr>
                </thead>
                <tbody class="text-center font-weight-bold">
                    <tr v-for="user in users" :key="user.id">
                        <td class="text-left py-3 px-4 font-weight-bold uppercase" style="background-color: #f9f9f9;">{{ user.name }}</td>
                        <td v-for="status in statuses" :key="status.id" class="py-3">
                            {{ getTotal(user.id, status.name) || '-' }}
                        </td>
                        <td class="py-3 text-primary">{{ getUserTotal(user.id) }}</td>
                    </tr>
                    <tr class="thead-light">
                        <th class="text-right pr-4">Matrix Total:</th>
                        <th v-for="status in statuses" :key="'sum-'+status.id">
                            {{ results.filter(r => r.call_status === status.name).reduce((sum, current) => sum + current.total, 0) }}
                        </th>
                        <th class="bg-info text-white">{{ results.reduce((sum, r) => sum + r.total, 0) }}</th>
                    </tr>
                </tbody>
            </table>

            <div class="row m-t-30 no-break">
                <div class="col-12 p-4 text-center border-top border-dark-dashed mt-5">
                    <p class="small font-italic">Internal CRM Performance Report - Confidential Metadata Analysis</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Global styles for this printable component only */
@media screen {
    body { background-color: #efefef; padding: 30px; }
    .printable-container { background-color: white; margin: auto; padding: 20px 0; }
}

@media print {
    body { background-color: white !important; padding: 0 !important; }
    .no-print { display: none !important; }
    .table-bordered th, .table-bordered td { border: 1px solid rgba(0,0,0,.4) !important; color: black !important; }
    .bg-dark { background-color: #343a40 !important; color: white !important;  }
    .bg-info { background-color: #17a2b8 !important; color: white !important; }
    .text-primary { color: #007bff !important; }
}

.table td, .table th { vertical-align: middle !important; }
.m-b-30{ margin-bottom: 30px; }
.m-t-30{ margin-top: 30px; }
.company-name{ border-bottom: 2px solid #000; }
.btn-group { width: 95%; margin: 20px auto; text-align: right; }
.bg-gray { background-color: #e2e6ea; }
.no-break { page-break-inside: avoid; }
</style>
