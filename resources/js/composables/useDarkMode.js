import { ref, watch } from 'vue';

// Global dark mode state
const darkMode = ref(false);

// Initialize dark mode from localStorage
const initializeDarkMode = () => {
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        darkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        darkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
};

// Watch for dark mode changes and persist to localStorage
watch(darkMode, (newValue) => {
    if (newValue) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    }
});

export function useDarkMode() {
    const toggleDarkMode = () => {
        darkMode.value = !darkMode.value;
    };

    return {
        darkMode,
        toggleDarkMode,
        initializeDarkMode
    };
}
