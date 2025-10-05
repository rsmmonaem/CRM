import { ref, watch } from 'vue';

// Global sidebar compact state
const sidebarCompact = ref(false);

// Initialize sidebar compact from localStorage
const initializeSidebarCompact = () => {
    const savedCompactMode = localStorage.getItem('sidebarCompact');
    if (savedCompactMode === 'true') {
        sidebarCompact.value = true;
    } else {
        sidebarCompact.value = false;
    }
};

// Watch for sidebar compact changes and persist to localStorage
watch(sidebarCompact, (newValue) => {
    localStorage.setItem('sidebarCompact', newValue.toString());
});

export function useSidebarCompact() {
    const toggleSidebarCompact = () => {
        sidebarCompact.value = !sidebarCompact.value;
    };

    return {
        sidebarCompact,
        toggleSidebarCompact,
        initializeSidebarCompact
    };
}
