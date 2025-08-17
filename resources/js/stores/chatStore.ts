import type { Message } from '@/types/claude';
import { reactive } from 'vue';

interface ChatStore {
    messages: Message[];
    currentSessionId: string | null;
    currentSessionFilename: string | null;
}

export const chatStore = reactive<ChatStore>({
    messages: [],
    currentSessionId: null,
    currentSessionFilename: null,
});

export function useChatStore() {
    const clearMessages = () => {
        chatStore.messages = [];
    };

    const setSession = (sessionId: string | null, sessionFilename: string | null) => {
        chatStore.currentSessionId = sessionId;
        chatStore.currentSessionFilename = sessionFilename;
    };

    const addMessage = (message: Message) => {
        chatStore.messages.push(message);
    };

    const updateMessage = (messageId: number, updates: Partial<Message>) => {
        const index = chatStore.messages.findIndex((m) => m.id === messageId);
        if (index !== -1) {
            chatStore.messages[index] = { ...chatStore.messages[index], ...updates };
        }
    };

    const appendToMessage = (messageId: number, text: string, rawResponse?: any) => {
        const index = chatStore.messages.findIndex((m) => m.id === messageId);
        if (index !== -1) {
            const message = chatStore.messages[index];
            message.content += text;

            if (rawResponse) {
                if (!message.rawResponses) {
                    message.rawResponses = [];
                }
                message.rawResponses.push(rawResponse);
            }

            // Trigger reactivity
            chatStore.messages[index] = { ...message };
        }
    };

    return {
        messages: chatStore.messages,
        currentSessionId: chatStore.currentSessionId,
        currentSessionFilename: chatStore.currentSessionFilename,
        clearMessages,
        setSession,
        addMessage,
        updateMessage,
        appendToMessage,
    };
}
