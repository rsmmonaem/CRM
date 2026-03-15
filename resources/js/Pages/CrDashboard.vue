<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    metrics: Object,
    callResultSummary: Array,
    leadResultSummary: Array,
    users: Array,
    filters: Object,
    user: Object
});

const filterUserId = ref(props.filters.user_id || '');
const fromDate = ref(props.filters.from_date || '');
const toDate = ref(props.filters.to_date || '');

const applyFilters = () => {
    router.visit(route('cr-dashboard'), {
        data: {
            user_id: filterUserId.value,
            from_date: fromDate.value,
            to_date: toDate.value
        },
        preserveState: true,
        preserveScroll: true
    });
};

watch([filterUserId, fromDate, toDate], () => {
    applyFilters();
});
</script>

<template>
    <Head title="CR Dashboard" />

    <ModernLayout>
        <div class="cr-dashboard-container">
            <div class="header">CRM Dashboard</div>



            <div class="filter-bar">
                <select v-if="user.role === 'admin'" v-model="filterUserId">
                    <option value="">All Users</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>

                <input type="date" placeholder="From Date" v-model="fromDate">
                <input type="date" placeholder="To Date" v-model="toDate">
            </div>

            <div class="section">
                <div class="section-title">Lead Details</div>

                <div class="cards">
                    <div class="card blue">Total Assign Lead<div class="number">{{ metrics.totalAssignLead }}</div></div>
                    <div class="card orange">Total Call<div class="number">{{ metrics.totalCall }}</div></div>
                    <div class="card yellow">Assain Lead Pending<div class="number">{{ metrics.pendingCall }}</div></div>
                    <div class="card purple">Repeat Call<div class="number">{{ metrics.repeatCall }}</div></div>
                    <div class="card green">Total Number Call<div class="number">{{ metrics.totalNumberCall }}</div></div>
                </div>
            </div>



            <div class="section">
                <div class="section-title">Crm Lead Result Summery</div>

                <div class="cards" v-if="leadResultSummary.length > 0">
                    <div v-for="(status, index) in leadResultSummary" :key="index" :class="['card', status.color]">
                        {{ status.name }}
                        <div class="number">{{ status.count }}</div>
                    </div>
                </div>
                <div v-else class="text-gray-500 mt-2">No lead statuses found in database.</div>
            </div>

            <div class="section">
                <div class="section-title">CRM Call Result Summary</div>

                <div class="cards" v-if="callResultSummary.length > 0">
                    <div v-for="(status, index) in callResultSummary" :key="index" :class="['card', status.color]">
                        {{ status.name }}
                        <div class="number">{{ status.count }}</div>
                    </div>
                </div>
                <div v-else class="text-gray-500 mt-2">No call statuses found in database.</div>
            </div>

            <div class="section">
                <div class="section-title">Follow Up Details</div>

                <div class="cards">
                    <div class="card yellow">Today Followup<div class="number">{{ metrics.todayFollowup }}</div></div>
                    <div class="card green">Pending Followup<div class="number">{{ metrics.pendingFollowup }}</div></div>
                    <div class="card darkgreen">Upcoming Calls<div class="number">{{ metrics.upcomingCalls }}</div></div>
                    <div class="card cyan">Today Visit<div class="number">{{ metrics.todayVisit }}</div></div>
                    <div class="card orange">Total Visit<div class="number">{{ metrics.totalVisit }}</div></div>
                </div>
            </div>
        </div>
    </ModernLayout>
</template>

<style scoped>
.cr-dashboard-container {
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:#eef2f6; /* slightly lighter in normal mode */
    border-radius: 8px;
    overflow: hidden;
    padding-bottom: 20px;
}

.header{
    background:linear-gradient(90deg,#00b4db,#0083b0);
    color:white;
    padding:20px;
    text-align:center;
    font-size:34px;
    font-weight:bold;
}

.filter-bar{
    display:flex;
    gap:15px;
    padding:15px;
    background:white;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.filter-bar select, .filter-bar input{
    padding:8px;
    border-radius:5px;
    border:1px solid #ccc;
    outline: none;
    background: #fff;
}

.section{
    padding:20px 20px 0 20px;
}

.section-title{
    font-size:18px;
    font-weight:bold;
    margin-bottom:10px;
    color: #333;
}

.cards{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
}

.card{
    padding:20px;
    border-radius:10px;
    color:white;
    font-weight:bold;
    text-align:center;
    transition:0.3s;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.card:hover{
    transform:translateY(-5px);
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
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

.number{
    font-size:28px;
    margin-top:10px;
}

/* Dark mode handling */
:global(.dark) .cr-dashboard-container {
    background: #1f2937;
}
:global(.dark) .section-title {
    color: #f3f4f6;
}
:global(.dark) .filter-bar {
    background: #374151;
    box-shadow: none;
}
:global(.dark) .filter-bar select, 
:global(.dark) .filter-bar input {
    background: #4b5563;
    color: white;
    border-color: #6b7280;
}
</style>
