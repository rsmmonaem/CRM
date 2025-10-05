<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(true);
const darkMode = ref(true); // Default to dark mode like RealBiz

const user = computed(() => page.props.auth.user);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    }
};

// Initialize dark mode from localStorage
onMounted(() => {
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true' || savedDarkMode === null) {
        darkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        darkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
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
                href: route('call-log.index'),
                icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                current: route().current('call-log.*'),
                permission: { module: 'lead_details', action: 'view' }
            },
            {
                name: 'Services',
                href: route('services.index'),
                icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                current: route().current('services.*'),
                permission: { module: 'services', action: 'view' }
            },
            {
                name: 'Status',
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
    <div class="min-h-screen bg-gray-900 dark:bg-gray-900">
        <!-- Mobile sidebar -->
        <div v-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="toggleSidebar"></div>
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-gray-800">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button
                        type="button"
                        class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        @click="toggleSidebar"
                    >
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <SidebarContent :navigation="navigation" :user="user" :darkMode="darkMode" @toggle-dark-mode="toggleDarkMode" />
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64">
                <SidebarContent :navigation="navigation" :user="user" :darkMode="darkMode" @toggle-dark-mode="toggleDarkMode" />
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-64 flex flex-col flex-1">
            <!-- Top navigation -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-gray-800 border-b border-gray-700">
                <button
                    type="button"
                    class="px-4 border-r border-gray-700 text-gray-300 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
                    @click="toggleSidebar"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-white">N</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h1 class="text-xl font-bold text-white">N. I. Biz Soft RealBizDev</h1>
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <!-- Search icon -->
                        <button class="p-2 text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        
                        <!-- Notification icon -->
                        <button class="p-2 text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>

                        <!-- User dropdown -->
                        <div class="ml-3 relative">
                            <div>
                                <button type="button" class="max-w-xs bg-gray-800 flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-gray-800 border-t border-gray-700">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-300">
                            Powered by <span class="text-yellow-400 font-semibold">NibizSoft</span>
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';

// Sidebar content component
const SidebarContent = {
    components: {
        Link
    },
    props: ['navigation', 'user', 'darkMode'],
    emits: ['toggle-dark-mode'],
    template: `
        <div class="flex flex-col h-0 flex-1 border-r border-gray-700 bg-gray-800">
            <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 mb-6">
                    <div class="h-8 w-8 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-white">N</span>
                    </div>
                    <div class="ml-3">
                        <h2 class="text-lg font-bold text-white">RealBiz ERP</h2>
                        <p class="text-xs text-gray-300">V9.1</p>
                    </div>
                </div>

                <!-- User Profile Section -->
                <div class="px-4 mb-6">
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ user?.name || 'User' }}</p>
                            <p class="text-xs text-gray-300">{{ user?.email || 'user@example.com' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Light Mode Toggle -->
                <div class="px-4 mb-6">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Light Mode</span>
                        <button
                            @click="$emit('toggle-dark-mode')"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                                darkMode ? 'bg-indigo-600' : 'bg-gray-200'
                            ]"
                        >
                            <span 
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    darkMode ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- Main Navigation -->
                <nav class="flex-1 px-2 space-y-1">
                    <div class="mb-4">
                        <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</h3>
                    </div>
                    
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                            'group flex items-center px-3 py-2 text-sm font-medium rounded-md'
                        ]"
                    >
                        <svg
                            :class="[
                                item.current
                                    ? 'text-white'
                                    : 'text-gray-400 group-hover:text-white',
                                'mr-3 flex-shrink-0 h-5 w-5'
                            ]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        {{ item.name }}
                    </Link>
                </nav>
            </div>
        </div>
    `
};

export default {
    components: {
        SidebarContent
    }
};
</script>