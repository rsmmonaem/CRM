<script setup>
import ModernLayout from '@/Layouts/ModernLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    user: Object,
    permissions: Object,
});

const page = usePage();

// Safe user data with fallback
const safeUser = computed(() => {
    return props.user || {
        id: null,
        name: '',
        email: '',
        role: 'user',
        permissions: [],
    };
});

// User info form
const userForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

// Permissions form
const permissionsForm = useForm({
    permissions: [],
});

// Safe permissions
const safePermissions = computed(() => {
    return props.permissions || {};
});

// Initialize user form
const initializeUserForm = () => {
    if (safeUser.value.id) {
        userForm.name = safeUser.value.name || '';
        userForm.email = safeUser.value.email || '';
        userForm.role = safeUser.value.role || 'user';
    }
};

// Initialize permissions
const initializePermissions = () => {
    if (safeUser.value.permissions && safeUser.value.permissions.length > 0) {
        const permissionIds = safeUser.value.permissions.map(p => p.id);
        permissionsForm.permissions = permissionIds;
    } else {
        permissionsForm.permissions = [];
    }
};

// Watch for safeUser changes
watch(() => safeUser.value, () => {
    if (safeUser.value.id) {
        initializeUserForm();
        initializePermissions();
    }
}, { immediate: true });

// Submit user info
const submitUserInfo = () => {
    if (!safeUser.value.id) return;

    userForm.put(route('users.update', safeUser.value.id), {
        onSuccess: () => {
            userForm.password = '';
            userForm.password_confirmation = '';
        },
        onError: (errors) => console.error('User update errors:', errors),
    });
};

// Submit permissions
const submitPermissions = () => {
    if (!safeUser.value.id) return;

    permissionsForm.put(route('users.update', safeUser.value.id), {
        onSuccess: () => {},
        onError: (errors) => console.error('Permission update errors:', errors),
    });
};

// Current user for permissions
const currentUser = computed(() => page.props.auth?.user || {});

// Check if user can edit
const canEditUser = computed(() => {
    return currentUser.value.is_admin || currentUser.value.permissions?.users?.includes('edit');
});

// Check if user can manage permissions
const canManagePermissions = computed(() => !!currentUser.value.is_admin);
</script>

<template>
    <Head title="Edit User" />

    <ModernLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 rounded-xl p-8 text-white shadow-xl">
                <h1 class="text-4xl font-bold mb-3">Edit User</h1>
                <p class="text-purple-100 text-lg">Update user information and permissions</p>
            </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="page.props.flash.success" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash.error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ page.props.flash.error }}
        </div>

        <!-- User Info Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">User Information</h2>
            </div>
            <div class="p-6">
                <form @submit.prevent="submitUserInfo">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                            <input
                                id="name"
                                v-model="userForm.name"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': userForm.errors.name }"
                                placeholder="Enter full name"
                            />
                            <div v-if="userForm.errors.name" class="mt-1 text-sm text-red-600">{{ userForm.errors.name }}</div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                            <input
                                id="email"
                                v-model="userForm.email"
                                type="email"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': userForm.errors.email }"
                                placeholder="Enter email address"
                            />
                            <div v-if="userForm.errors.email" class="mt-1 text-sm text-red-600">{{ userForm.errors.email }}</div>
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                            <select
                                id="role"
                                v-model="userForm.role"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': userForm.errors.role }"
                            >
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div v-if="userForm.errors.role" class="mt-1 text-sm text-red-600">{{ userForm.errors.role }}</div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password (Optional)</label>
                            <input
                                id="password"
                                v-model="userForm.password"
                                type="password"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': userForm.errors.password }"
                                placeholder="Enter new password"
                            />
                            <div v-if="userForm.errors.password" class="mt-1 text-sm text-red-600">{{ userForm.errors.password }}</div>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="md:col-span-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                            <input
                                id="password_confirmation"
                                v-model="userForm.password_confirmation"
                                type="password"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': userForm.errors.password_confirmation }"
                                placeholder="Confirm new password"
                            />
                            <div v-if="userForm.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ userForm.errors.password_confirmation }}</div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="userForm.processing"
                            class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-purple-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="userForm.processing">Updating...</span>
                            <span v-else>Update User Info</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Permissions Management -->
        <div v-if="canManagePermissions" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Permissions Management</h2>
            </div>
            <div class="p-6">
                <form @submit.prevent="submitPermissions">
                    <div class="space-y-6">
                        <div v-for="(modulePermissions, module) in safePermissions" :key="module" class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 capitalize">{{ module }} Permissions</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label v-for="permission in modulePermissions" :key="permission.id" class="flex items-center space-x-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="permissionsForm.permissions"
                                        :value="permission.id"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300 capitalize">{{ permission.action }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="permissionsForm.processing"
                            class="bg-gradient-to-r from-green-500 to-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-green-600 hover:to-blue-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="permissionsForm.processing">Updating Permissions...</span>
                            <span v-else>Update Permissions</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Access Denied -->
        <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Permission Required</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>You need admin privileges to manage user permissions.</p>
                    </div>
                </div>
            </div>
        </div>
    </ModernLayout>
</template>
