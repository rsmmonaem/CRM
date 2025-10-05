<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    statuses: Array,
});

const deleteStatus = (status) => {
    if (confirm('Are you sure you want to delete this status?')) {
        router.delete(route('statuses.destroy', status.id));
    }
};
</script>

<template>
    <Head title="Statuses" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 rounded-xl p-8 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-3">Lead Statuses Management</h1>
                        <p class="text-orange-100 text-lg">Manage lead status categories</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link 
                            :href="route('statuses.create')" 
                            class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200 flex items-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add New Status</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statuses Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white">All Statuses</h3>
                    </div>
                    <div class="text-white text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full">
                        {{ statuses.length }} statuses
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div v-if="statuses.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No statuses found.</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Create your first status to get started.</p>
                    <Link 
                        :href="route('statuses.create')" 
                        class="mt-4 inline-block bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-200"
                    >
                        Create Your First Status
                    </Link>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="status in statuses" :key="status.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ status.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ status.creator.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(status.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <Link
                                            :href="route('statuses.show', status.id)"
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-lg text-xs transition-colors duration-200"
                                        >
                                            👁️ View
                                        </Link>
                                        <Link
                                            :href="route('statuses.edit', status.id)"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg text-xs transition-colors duration-200"
                                        >
                                            ✏️ Edit
                                        </Link>
                                        <button
                                            @click="deleteStatus(status)"
                                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg text-xs transition-colors duration-200"
                                        >
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModernLayout>
</template>
