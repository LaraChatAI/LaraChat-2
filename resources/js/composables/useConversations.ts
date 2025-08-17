import axios from 'axios';
import { computed, onUnmounted, ref } from 'vue';

interface Conversation {
    id: number;
    user_id: number;
    title: string;
    repository: string | null;
    project_directory: string | null;
    claude_session_id: string | null;
    is_processing: boolean;
    created_at: string;
    updated_at: string;
}

// Move these outside the composable function to share state across all instances
const conversations = ref<Conversation[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
let refreshInterval: number | null = null;
let hasInitialized = false;
let lastFetchTime = 0;

export function useConversations() {
    const fetchConversations = async (silent = false, force = false) => {
        // Prevent too frequent fetches (within 500ms)
        const now = Date.now();
        if (now - lastFetchTime < 500 && !force) {
            return;
        }
        lastFetchTime = now;
        
        // Skip fetching if already initialized and not forcing or silent refresh
        if (hasInitialized && !force && !silent && conversations.value.length > 0) {
            return;
        }

        if (!silent) {
            loading.value = true;
        }
        error.value = null;

        try {
            const response = await axios.get<Conversation[]>('/api/claude/conversations');
            conversations.value = response.data;
            hasInitialized = true;

            // Check if any conversations are processing
            const hasProcessing = response.data.some((conv) => conv.is_processing);

            // Set up or clear interval based on processing status
            if (hasProcessing && !refreshInterval) {
                // Start periodic refresh every 2 seconds
                refreshInterval = window.setInterval(() => {
                    fetchConversations(true, true); // Silent refresh, forced to get latest
                }, 2000);
            } else if (!hasProcessing && refreshInterval) {
                // Clear interval if no conversations are processing
                window.clearInterval(refreshInterval);
                refreshInterval = null;
            }
        } catch (err) {
            error.value = 'Failed to fetch conversations';
            console.error('Error fetching conversations:', err);
        } finally {
            if (!silent) {
                loading.value = false;
            }
        }
    };

    // Check if any conversation is processing
    const hasProcessingConversations = computed(() => {
        return conversations.value.some((conv) => conv.is_processing);
    });

    // Start polling for updates
    const startPolling = (intervalMs = 2000) => {
        if (refreshInterval) {
            window.clearInterval(refreshInterval);
        }
        refreshInterval = window.setInterval(() => {
            fetchConversations(true, true); // Silent refresh, forced
        }, intervalMs);
    };
    
    // Stop polling
    const stopPolling = () => {
        if (refreshInterval) {
            window.clearInterval(refreshInterval);
            refreshInterval = null;
        }
    };
    
    // Clean up interval on component unmount
    const cleanup = () => {
        stopPolling();
    };

    // Ensure cleanup when composable is no longer used
    onUnmounted(() => {
        cleanup();
    });

    return {
        conversations,
        loading,
        error,
        fetchConversations,
        hasProcessingConversations,
        startPolling,
        stopPolling,
        cleanup,
    };
}
