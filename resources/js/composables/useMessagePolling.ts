import { useChatStore } from '@/stores/chatStore';
import type { SessionConversation } from '@/types/claude';
import { extractTextFromResponse } from '@/utils/claudeResponseParser';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

export function useMessagePolling(sessionFilename: ref<string | null>) {
    const chatStore = useChatStore();
    let pollInterval: NodeJS.Timeout | null = null;

    const pollForNewMessages = async () => {
        if (!sessionFilename.value || !chatStore.currentSessionId) return;

        try {
            // Fetch the session data
            const response = await axios.get(`/api/claude/sessions/${sessionFilename.value}`);
            const sessionData: SessionConversation[] = response.data;

            // Get the last message timestamp from our store
            const lastMessage = chatStore.messages[chatStore.messages.length - 1];
            const lastMessageTime = lastMessage ? lastMessage.timestamp.getTime() : 0;

            // Process only new conversations
            for (const conversation of sessionData) {
                const conversationTime = new Date(conversation.timestamp).getTime();

                // Skip if this conversation is older than our last message
                if (conversationTime <= lastMessageTime) continue;

                // Check if we already have this user message
                const hasUserMessage = chatStore.messages.some(
                    (m) => m.role === 'user' && m.content === conversation.userMessage && Math.abs(m.timestamp.getTime() - conversationTime) < 1000,
                );

                if (!hasUserMessage) {
                    // Add user message
                    chatStore.addMessage({
                        id: Date.now() + Math.random(),
                        content: conversation.userMessage,
                        role: 'user',
                        timestamp: new Date(conversation.timestamp),
                    });

                    // Add assistant responses
                    if (conversation.rawJsonResponses?.length) {
                        for (let i = 0; i < conversation.rawJsonResponses.length; i++) {
                            const rawResponse = conversation.rawJsonResponses[i];
                            const content = extractTextFromResponse(rawResponse);

                            chatStore.addMessage({
                                id: Date.now() + Math.random() + i,
                                content: content || `[${rawResponse.type || 'unknown'} response]`,
                                role: 'assistant',
                                timestamp: new Date(conversation.timestamp),
                                rawResponses: [rawResponse],
                            });
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error polling for new messages:', error);
        }
    };

    const startPolling = () => {
        // Poll every 2 seconds
        pollInterval = setInterval(pollForNewMessages, 2000);
    };

    const stopPolling = () => {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    };

    onMounted(() => {
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        pollForNewMessages,
        startPolling,
        stopPolling,
    };
}
