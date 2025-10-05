<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Search across all data...'
    },
    debounceMs: {
        type: Number,
        default: 300
    }
});

const emit = defineEmits(['search', 'clear']);

const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref([]);
const searchHistory = ref([]);
const showResults = ref(false);
const selectedIndex = ref(-1);

let debounceTimeout = null;

// Search categories
const searchCategories = ref([
    { key: 'leads', label: 'Leads', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { key: 'users', label: 'Users', icon: 'M12 4.354a4 4 0 110 5.292M12 20.052v-8.69M12 20.052H10M12 20.052h2' },
    { key: 'services', label: 'Services', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z' },
    { key: 'statuses', label: 'Statuses', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' }
]);

const performSearch = async (query) => {
    if (!query.trim()) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    isSearching.value = true;
    
    try {
        // Make API call to search endpoint
        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (response.ok) {
            const data = await response.json();
            searchResults.value = data.results || [];
            showResults.value = true;
            
            // Add to search history
            if (!searchHistory.value.includes(query)) {
                searchHistory.value.unshift(query);
                if (searchHistory.value.length > 10) {
                    searchHistory.value.pop();
                }
            }
        }
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const debouncedSearch = (query) => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
    
    debounceTimeout = setTimeout(() => {
        performSearch(query);
    }, props.debounceMs);
};

const handleInput = (event) => {
    searchQuery.value = event.target.value;
    debouncedSearch(searchQuery.value);
};

const handleKeydown = (event) => {
    if (!showResults.value || searchResults.value.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, searchResults.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (selectedIndex.value >= 0) {
                selectResult(searchResults.value[selectedIndex.value]);
            }
            break;
        case 'Escape':
            showResults.value = false;
            selectedIndex.value = -1;
            break;
    }
};

const selectResult = (result) => {
    showResults.value = false;
    selectedIndex.value = -1;
    
    // Navigate to the result
    if (result.url) {
        router.visit(result.url);
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    showResults.value = false;
    selectedIndex.value = -1;
    emit('clear');
};

const selectHistoryItem = (item) => {
    searchQuery.value = item;
    performSearch(item);
};

const getResultIcon = (category) => {
    const cat = searchCategories.value.find(c => c.key === category);
    return cat ? cat.icon : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
};

const getResultColor = (category) => {
    const colors = {
        leads: 'text-blue-600 bg-blue-50',
        users: 'text-green-600 bg-green-50',
        services: 'text-purple-600 bg-purple-50',
        statuses: 'text-orange-600 bg-orange-50'
    };
    return colors[category] || 'text-gray-600 bg-gray-50';
};

// Close results when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.search-container')) {
        showResults.value = false;
        selectedIndex.value = -1;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

watch(searchQuery, (newQuery) => {
    if (!newQuery.trim()) {
        showResults.value = false;
        searchResults.value = [];
    }
});
</script>

<template>
    <div class="search-container relative w-full max-w-2xl mx-auto">
        <!-- Search Input -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                v-model="searchQuery"
                @input="handleInput"
                @keydown="handleKeydown"
                @focus="showResults = true"
                type="text"
                :placeholder="placeholder"
                class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div v-else-if="isSearching" class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
            </div>
        </div>

        <!-- Search Results Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showResults && (searchResults.length > 0 || searchHistory.length > 0)"
                class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-96 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
            >
                <!-- Search Results -->
                <div v-if="searchResults.length > 0">
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        Search Results ({{ searchResults.length }})
                    </div>
                    <div
                        v-for="(result, index) in searchResults"
                        :key="`result-${index}`"
                        @click="selectResult(result)"
                        :class="[
                            'cursor-pointer select-none relative py-3 px-4 hover:bg-gray-50',
                            selectedIndex === index ? 'bg-blue-50 text-blue-900' : 'text-gray-900'
                        ]"
                    >
                        <div class="flex items-center">
                            <div :class="['flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center', getResultColor(result.category)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getResultIcon(result.category)" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium truncate">{{ result.title }}</p>
                                    <span class="text-xs text-gray-500 capitalize">{{ result.category }}</span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ result.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search History -->
                <div v-else-if="searchHistory.length > 0 && !searchQuery">
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        Recent Searches
                    </div>
                    <div
                        v-for="(item, index) in searchHistory.slice(0, 5)"
                        :key="`history-${index}`"
                        @click="selectHistoryItem(item)"
                        class="cursor-pointer select-none relative py-2 px-4 text-gray-900 hover:bg-gray-50"
                    >
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">{{ item }}</span>
                        </div>
                    </div>
                </div>

                <!-- No Results -->
                <div v-if="searchQuery && searchResults.length === 0 && !isSearching" class="px-4 py-3 text-sm text-gray-500 text-center">
                    No results found for "{{ searchQuery }}"
                </div>
            </div>
        </Transition>
    </div>
</template>
