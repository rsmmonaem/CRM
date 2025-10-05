<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SearchSystem from '@/Components/SearchSystem.vue';
import { useDarkMode } from '@/composables/useDarkMode.js';
import { useSidebarCompact } from '@/composables/useSidebarCompact.js';

const page = usePage();
const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);
const compactButtonClicked = ref(false);

// Use the composables
const { darkMode, toggleDarkMode, initializeDarkMode } = useDarkMode();
const { sidebarCompact, toggleSidebarCompact, initializeSidebarCompact } = useSidebarCompact();

const user = computed(() => page.props.auth.user);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCompactToggle = () => {
    toggleSidebarCompact();

    // Show click feedback
    compactButtonClicked.value = true;
    setTimeout(() => {
        compactButtonClicked.value = false;
    }, 3000);
};



const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    // Initialize dark mode and sidebar compact from localStorage
    initializeDarkMode();
    initializeSidebarCompact();
});

const navigation = computed(() => {
    try {
        // Define all possible navigation items with their permission requirements
        const allNavigationItems = [
            {
                name: 'Dashboard',
                href: route('dashboard'),
                icon: 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z',
                current: route().current('dashboard'),
                permission: null // Always visible
            },
            {
                name: 'Leads',
                href: route('leads.index'),
                icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                current: route().current('leads.*'),
                permission: { module: 'leads', action: 'view' }
            },
            {
                name: 'Call Log',
                href: route('call-logs.index'),
                icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                current: route().current('call-logs.*'),
                permission: { module: 'leads', action: 'view' }
            },
            {
                name: 'Services',
                href: route('services.index'),
                icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                current: route().current('services.*'),
                permission: { module: 'services', action: 'view' }
            },
            {
                name: 'Statuses',
                href: route('statuses.index'),
                icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                current: route().current('statuses.*'),
                permission: { module: 'statuses', action: 'view' }
            },
            {
                name: 'User Management',
                href: route('users.index'),
                icon: 'M12 4.354a4 4 0 110 5.292M12 20.052v-8.69M12 20.052H10M12 20.052h2',
                current: route().current('users.*'),
                permission: { module: 'users', action: 'view' }
            }
        ];

        // Filter navigation items based on user permissions
        return allNavigationItems.filter(item => {
            // Always show items without permission requirements
            if (!item.permission) return true;

            // If no user data, don't show the item
            if (!user.value) return false;

            // Admin users see everything
            if (user.value.is_admin) return true;

            // Check if user has the required permission
            return user.value.permissions?.[item.permission.module]?.includes(item.permission.action) || false;
        });
    } catch (error) {
        console.error('Navigation computed property error:', error);
        // Return minimal navigation on error
        return [
            {
                name: 'Dashboard',
                href: '/dashboard',
                icon: 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z',
                current: false,
                permission: null
            }
        ];
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">

        <!-- Mobile sidebar (overlay) -->
        <div v-show="sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden">
            <!-- Backdrop with animation -->
            <transition
                enter-active-class="transition-opacity ease-linear duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-show="sidebarOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="toggleSidebar"></div>
            </transition>

            <!-- Sidebar with slide animation -->
            <transition
                enter-active-class="transition ease-in-out duration-300 transform"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition ease-in-out duration-300 transform"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div v-show="sidebarOpen" class="relative flex flex-col w-64 bg-white dark:bg-gray-800 shadow-xl">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button
                        type="button"
                        class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        @click="toggleSidebar"
                    >
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <!-- Sidebar content -->
                <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center px-4">
                        <ApplicationLogo class="h-8 w-auto" />
                        <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">CRM</span>
                    </div>
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                item.current
                                    ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-900 dark:text-indigo-100'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white',
                                'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
                            ]"
                        >
                            <svg
                                :class="[
                                    item.current
                                        ? 'text-indigo-500 dark:text-indigo-400'
                                        : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                    'mr-3 h-6 w-6 flex-shrink-0'
                                ]"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                            </svg>
                            {{ item.name }}
                        </Link>
                    </nav>
                </div>
                <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4 flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ user?.name || 'User' }}</p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ user?.role || 'user' }}</p>
                    </div>
                </div>
            </div>
            </transition>
        </div>

        <!-- Static sidebar for desktop -->
        <div :class="[
            'hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out',
            sidebarCompact ? 'lg:w-16' : 'lg:w-64'
        ]">
            <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
                <div class="flex items-center px-4">
                    <ApplicationLogo class="h-8 w-auto" />
                    <transition
                        enter-active-class="transition-opacity duration-300"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-300"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-show="!sidebarCompact" class="ml-2 text-xl font-bold text-gray-900 dark:text-white">CRM</span>
                    </transition>
                </div>
                <nav class="mt-5 flex-1 px-2 space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-900 dark:text-indigo-100'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white',
                            'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-all duration-200',
                            sidebarCompact ? 'justify-center' : ''
                        ]"
                        :title="sidebarCompact ? item.name : ''"
                    >
                        <svg
                            :class="[
                                item.current
                                    ? 'text-indigo-500 dark:text-indigo-400'
                                    : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                'h-6 w-6 flex-shrink-0',
                                sidebarCompact ? '' : 'mr-3'
                            ]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
                        </svg>
                        <transition
                            enter-active-class="transition-opacity duration-300"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-300"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <span v-show="!sidebarCompact" class="truncate">{{ item.name }}</span>
                        </transition>
                    </Link>
                </nav>
            </div>
            <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4 flex items-center">
                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                    <span class="text-sm font-medium text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                </div>
                <transition
                    enter-active-class="transition-opacity duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="!sidebarCompact" class="ml-3">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ user?.name || 'User' }}</p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ user?.role || 'user' }}</p>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Main content -->
        <div :class="[
            'flex flex-col flex-1 transition-all duration-300 ease-in-out',
            sidebarCompact ? 'lg:pl-16' : 'lg:pl-64'
        ]">
            <!-- Top bar -->
            <div class="relative z-10 flex h-16 flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <!-- Desktop compact toggle button -->
                <button
                    type="button"
                    :class="[
                        'hidden lg:block px-4 border-r border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-all duration-300',
                        compactButtonClicked ? 'bg-green-100 dark:bg-green-900 bg-opacity-50' : ''
                    ]"
                    @click="handleSidebarCompactToggle"
                    :title="sidebarCompact ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <span class="sr-only">{{ sidebarCompact ? 'Expand sidebar' : 'Collapse sidebar' }}</span>
                    <svg class="h-6 w-6 transition-transform duration-200" :class="{ 'rotate-180': sidebarCompact }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
                <!-- Search System -->
                <div class="flex-1 flex items-center justify-center px-4">
                    <SearchSystem placeholder="Search leads, users, services..." />
                </div>
                <!-- Mobile menu button -->
                <button
                    type="button"
                    class="px-4 border-r border-gray-200 dark:border-gray-700 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
                    @click="toggleSidebar"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </button>


                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex"></div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <!-- Dark mode -->
                        <button
                            @click="toggleDarkMode"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                                darkMode ? 'bg-indigo-600' : 'bg-gray-200'
                            ]"
                        >
                            <span class="sr-only">Toggle dark mode</span>
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    darkMode ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            ></span>
                        </button>

                        <!-- User menu -->
                        <div class="ml-3 relative">
                            <button
                                type="button"
                                class="max-w-xs bg-white dark:bg-gray-800 flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @click="userDropdownOpen = !userDropdownOpen"
                            >
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                                </div>
                            </button>
                            <div v-show="userDropdownOpen" @click="userDropdownOpen = false" class="fixed inset-0 z-10"></div>
                            <div v-show="userDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg py-1 z-20">
                                <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Profile</Link>
                                <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Log Out</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
