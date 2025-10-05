<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CallModal from '@/Components/CallModal.vue';
import NewLeadModal from '@/Components/NewLeadModal.vue';
import EditLeadModal from '@/Components/EditLeadModal.vue';

const props = defineProps({
    leads: Array,
    user: Object,
    services: Array,
    statuses: Array,
    users: Array,
});

const page = usePage();
const showCallModal = ref(false);
const selectedLead = ref(null);
const showEditModal = ref(false);
const editingLead = ref(null);
const showNewLeadModal = ref(false);
const callButtonClicked = ref(new Set());
const viewButtonClicked = ref(new Set());
const editButtonClicked = ref(new Set());

const user = computed(() => page.props.auth.user);

const hasPermission = (module, action) => {
    if (user.value?.is_admin) return true;
    return user.value?.permissions?.[module]?.includes(action) || false;
};

const openCallModal = (lead) => {
    selectedLead.value = lead;
    showCallModal.value = true;

    // Show click feedback
    callButtonClicked.value.add(lead.id);
    setTimeout(() => {
        callButtonClicked.value.delete(lead.id);
    }, 3000);
};

const handleViewClick = (leadId) => {
    // Show click feedback
    viewButtonClicked.value.add(leadId);
    setTimeout(() => {
        viewButtonClicked.value.delete(leadId);
    }, 3000);
};


const closeCallModal = () => {
    showCallModal.value = false;
    selectedLead.value = null;
};

const openEditModal = (lead) => {
    editingLead.value = lead;
    showEditModal.value = true;

    // Show click feedback
    editButtonClicked.value.add(lead.id);
    setTimeout(() => {
        editButtonClicked.value.delete(lead.id);
    }, 3000);
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingLead.value = null;
};

const deleteLead = (lead) => {
    if (confirm('Are you sure you want to delete this lead?')) {
        router.delete(route('leads.destroy', lead.id));
    }
};

// New Lead Modal handlers
const openNewLeadModal = () => {
    showNewLeadModal.value = true;
};

const closeNewLeadModal = () => {
    showNewLeadModal.value = false;
};


const getStatusClass = (statusName) => {
    const name = (statusName || '').toLowerCase();

    if (name.includes('won') || name.includes('success')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }

    if (name.includes('lost') || name.includes('cancel')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }

    if (name.includes('contact')) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
    }

    if (name.includes('negotiation') || name.includes('deal')) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    }

    if (name.includes('proposal')) {
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    }

    if (name.includes('qualified')) {
        return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
    }

    if (name.includes('new') || name.includes('open')) {
        return 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200';
    }

    return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};
</script>

<template>
    <Head title="Leads" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-8 text-white shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Leads Management</h1>
                        <p class="text-blue-100 text-lg">Manage and track your leads efficiently</p>
                    </div>

                    <button
                        v-if="hasPermission('leads', 'create')"
                        @click="openNewLeadModal"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Add New Lead</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Leads Section -->
        <div class="dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6 ">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center space-x-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>All Leads</span>
                </h3>
                <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">{{ leads.length }} total</span>
            </div>

            <!-- Empty State -->
            <div v-if="leads.length === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg">No leads found.</p>
                <button
                    @click="openNewLeadModal"
                    class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                >
                    Create Your First Lead
                </button>
            </div>

            <!-- Lead Cards -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
  <div v-for="lead in leads" :key="lead.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl dark:hover:shadow-white/20 border border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-white/30 transition-all duration-300 p-4 flex flex-col justify-between">

    <!-- Header: Name + Status -->
    <div class="flex justify-between items-start mb-2">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-white truncate">
        {{ lead.name || 'Unnamed Lead' }}
      </h4>
      <span class="text-xs font-semibold px-2 py-1 rounded-full" :class="getStatusClass(lead.status.name)">
        {{ lead.status.name }}
      </span>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300 mb-2">
      <div class="truncate"><strong>Phone:</strong> {{ lead.phone || '-' }}</div>
      <div class="truncate"><strong>Email:</strong> {{ lead.email || '-' }}</div>
      <div class="truncate"><strong>Company:</strong> {{ lead.company_name || '-' }}</div>
      <div class="truncate"><strong>Service:</strong> {{ lead.service.name || '-' }}</div>
      <div class="truncate"><strong>Assigned:</strong> {{ lead.assigned_user.name || '-' }}</div>
      <div class="truncate"><strong>Created:</strong> {{ new Date(lead.created_at).toLocaleDateString() }}</div>
    </div>

    <!-- Last Call History (Left-Right) -->
    <div v-if="lead.lead_details.length > 0" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 text-xs text-gray-500 dark:text-gray-300">
      <div class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Last Call</div>
      <div v-for="(call, index) in lead.lead_details.slice(0, 1)" :key="call.id" class="flex justify-between items-start gap-2">
        <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
          {{ new Date(call.created_at).toLocaleDateString() }}
        </div>
        <div class="flex-1 text-gray-700 dark:text-gray-200 truncate">
          {{ call.call_followup_summary || '-' }}
        </div>
      </div>
      <div v-if="lead.lead_details.length > 1" class="text-right text-gray-400 dark:text-gray-500 text-xs">+{{ lead.lead_details.length - 1 }} more</div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-3 flex flex-wrap gap-2">
      <button @click="openCallModal(lead)" class="flex-1 bg-blue-500 hover:bg-blue-600 dark:hover:bg-blue-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Call</button>
      <Link :href="route('leads.show', lead.id)" class="flex-1 bg-green-500 hover:bg-green-600 dark:hover:bg-green-400 text-white font-semibold py-2 px-2 rounded text-xs text-center transition-all duration-200 hover:scale-105 hover:shadow-lg">View</Link>
      <button @click="openEditModal(lead)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 dark:hover:bg-yellow-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Edit</button>
      <button @click="deleteLead(lead)" class="flex-1 bg-red-500 hover:bg-red-600 dark:hover:bg-red-400 text-white font-semibold py-2 px-2 rounded text-xs transition-all duration-200 hover:scale-105 hover:shadow-lg">Delete</button>
    </div>

  </div>
</div>

        </div>

        <!-- Call Modal -->
        <CallModal
            v-if="showCallModal"
            :lead="selectedLead"
            @close="closeCallModal"
        />

        <!-- New Lead Modal -->
        <NewLeadModal
            v-if="showNewLeadModal"
            :show="showNewLeadModal"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeNewLeadModal"
        />

        <!-- Edit Lead Modal -->
        <EditLeadModal
            v-if="showEditModal"
            :lead="editingLead"
            :services="services"
            :statuses="statuses"
            :users="users"
            @close="closeEditModal"
        />
    </ModernLayout>
</template>
