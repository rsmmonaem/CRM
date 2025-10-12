<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo Section -->
            <div class="text-center">
                <Link href="/" class="inline-block">
                    <ApplicationLogo class="h-16 w-16 mx-auto fill-current text-indigo-600 hover:text-indigo-700 transition-colors duration-200" />
                </Link>
                <h2 class="mt-4 text-3xl font-bold text-gray-900">
                    Welcome back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sign in to your account to continue
                </p>
            </div>

            <!-- Form Container -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
