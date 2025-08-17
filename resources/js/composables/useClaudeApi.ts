import type { ClaudeApiRequest, SessionConversation } from '@/types/claude';
import axios from 'axios';
import { ref } from 'vue';

export function useClaudeApi() {
    const isLoading = ref(false);

    const sendMessageToApi = async (
        request: ClaudeApiRequest,
        onChunk: (text: string, rawResponse: any) => void,
    ): Promise<{ conversationId: number | null; sessionFilename: string | null } | null> => {
        const response = await fetch('/api/claude', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(request),
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to send message');
        }

        // Get JSON response
        const data = await response.json();

        // If it's a success response (job queued), notify the user
        if (data.success) {
            // Send a system message to indicate the message was queued
            onChunk('', {
                type: 'system',
                subtype: 'queued',
                message: data.message || 'Message queued for processing',
            });

            // Return the conversation ID and filename
            return {
                conversationId: data.conversationId || null,
                sessionFilename: data.sessionFilename || null,
            };
        }

        // Handle error response
        if (data.error) {
            throw new Error(data.error);
        }

        return null;
    };

    const loadSession = async (sessionFile: string): Promise<SessionConversation[]> => {
        const response = await axios.get(`/api/claude/sessions/${sessionFile}`);
        return response.data;
    };

    return {
        isLoading,
        sendMessageToApi,
        loadSession,
    };
}
