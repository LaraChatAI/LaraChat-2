import type { Message } from '@/types/claude';
import { extractTextFromResponse } from '@/utils/claudeResponseParser';
import { ref } from 'vue';

export function useChatMessages() {
    const messages = ref<Message[]>([]);

    const addUserMessage = (content: string): Message => {
        const message: Message = {
            id: Date.now(),
            content,
            role: 'user',
            timestamp: new Date(),
        };
        messages.value.push(message);
        return message;
    };

    const addAssistantMessage = (): Message => {
        const message: Message = {
            id: Date.now() + 1,
            content: '',
            role: 'assistant',
            timestamp: new Date(),
            rawResponses: [],
        };
        messages.value.push(message);
        return message;
    };

    const updateMessage = (messageId: number, updates: Partial<Message>) => {
        const index = messages.value.findIndex((m) => m.id === messageId);
        if (index !== -1) {
            messages.value[index] = { ...messages.value[index], ...updates };
        }
    };

    const appendToMessage = (messageId: number, text: string, rawResponse?: any) => {
        const index = messages.value.findIndex((m) => m.id === messageId);
        if (index !== -1) {
            const message = messages.value[index];

            if (rawResponse) {
                // Add the raw response
                if (!message.rawResponses) {
                    message.rawResponses = [];
                }
                message.rawResponses.push(rawResponse);

                // Rebuild content from all raw responses
                message.content = '';
                for (const response of message.rawResponses) {
                    const extractedText = extractTextFromResponse(response);
                    if (extractedText) {
                        message.content += extractedText;
                    }
                }
            } else if (text) {
                // Fallback to appending text if no raw response
                message.content += text;
            }

            // Trigger reactivity
            messages.value[index] = { ...message };
        }
    };

    const formatTime = (date: Date): string => {
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
    };

    return {
        messages,
        addUserMessage,
        addAssistantMessage,
        updateMessage,
        appendToMessage,
        formatTime,
    };
}
