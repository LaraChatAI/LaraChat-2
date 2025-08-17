import axios from 'axios';
import { ref } from 'vue';

export const claudeSessions = ref<
    Array<{ filename: string; name: string; userMessage: string; repository?: string; path: string; lastModified: number }>
>([]);

export function useClaudeSessions() {
    const isLoadingSessions = ref(false);

    const fetchSessions = async () => {
        try {
            isLoadingSessions.value = true;
            const response = await axios.get('/api/claude/sessions');
            claudeSessions.value = response.data;
        } catch (error) {
            console.error('Failed to fetch Claude sessions:', error);
        } finally {
            isLoadingSessions.value = false;
        }
    };

    const refreshSessions = () => {
        return fetchSessions();
    };

    return {
        claudeSessions,
        isLoadingSessions,
        fetchSessions,
        refreshSessions,
    };
}
