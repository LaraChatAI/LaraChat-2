<script setup lang="ts">
import ChatMessage from '@/components/ChatMessage.vue';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Textarea } from '@/components/ui/textarea';
import { useChatMessages } from '@/composables/useChatMessages';
import { useChatUI } from '@/composables/useChatUI';
import { useClaudeApi } from '@/composables/useClaudeApi';
import { useClaudeSessions } from '@/composables/useClaudeSessions';
import { useConversations } from '@/composables/useConversations';
import { useRepositories } from '@/composables/useRepositories';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { extractTextFromResponse } from '@/utils/claudeResponseParser';
import { router } from '@inertiajs/vue3';
import { Eye, EyeOff, GitBranch, Send } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    sessionFile?: string;
    repository?: string;
    conversationId?: number;
    sessionId?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Claude', href: '/claude' }];

// Composables
const { messagesContainer, textareaRef, scrollToBottom, adjustTextareaHeight, resetTextareaHeight, focusInput, setupFocusHandlers } = useChatUI();
const { messages, addUserMessage, addAssistantMessage, appendToMessage, formatTime } = useChatMessages();
const { isLoading, sendMessageToApi, loadSession } = useClaudeApi();
const { claudeSessions, refreshSessions } = useClaudeSessions();
const { fetchConversations } = useConversations();

// Local state
const inputMessage = ref('');
const sessionFilename = ref<string | null>(props.sessionFile || null);
const sessionId = ref<string | null>(props.sessionId || null);
const conversationId = ref<number | null>(props.conversationId || null);
const hideSystemMessages = ref(true);
const pollingInterval = ref<number | null>(null);
const lastMessageCount = ref(0);
const incompleteMessageFound = ref(false);
const selectedRepository = ref<string | null>(props.repository || null);

// Repository management
const { repositories, fetchRepositories } = useRepositories();
const selectedRepositoryData = computed(() => {
    if (!selectedRepository.value) return null;
    // Try to match by folder name in local_path or by exact name
    return repositories.value.find((r) => {
        // Check if the local_path ends with the selected repository name
        if (r.local_path && r.local_path.endsWith('/' + selectedRepository.value)) {
            return true;
        }
        // Also check for exact name match (case-insensitive)
        return r.name.toLowerCase() === selectedRepository.value.toLowerCase();
    });
});

// Setup focus handlers
setupFocusHandlers(isLoading);

// Computed properties
const filteredMessages = computed(() => {
    if (!hideSystemMessages.value) {
        return messages.value;
    }

    return messages.value.filter((message) => {
        // Always show user messages
        if (message.role === 'user') {
            return true;
        }

        // For assistant messages, check if they contain text content
        if (message.rawResponses && message.rawResponses.length > 0) {
            // Check if any of the raw responses are of type 'text'
            return message.rawResponses.some((response) => {
                if (response.type === 'assistant' && response.message?.content) {
                    return response.message.content.some((item: any) => item.type === 'text');
                }
                return false;
            });
        }

        // If no raw responses, show the message (it might be a regular text response)
        return true;
    });
});

const initializeSession = () => {
    // Don't set a session ID here - let Claude CLI generate it

    if (!sessionFilename.value) {
        if (props.sessionFile) {
            sessionFilename.value = props.sessionFile;
        } else {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
            const tempId = Date.now().toString(36);
            sessionFilename.value = `${timestamp}-session-${tempId}.json`;
        }
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isLoading.value) return;

    // Add user message
    const messageToSend = inputMessage.value;
    addUserMessage(messageToSend);

    // Clear input
    inputMessage.value = '';
    resetTextareaHeight();

    // Start loading
    isLoading.value = true;
    await scrollToBottom();

    // Initialize session
    initializeSession();

    // Add dummy session to sidebar immediately if this is a new session
    if (!props.sessionFile) {
        const dummySession = {
            filename: sessionFilename.value!,
            name: messageToSend.length > 30 ? messageToSend.substring(0, 30) + '...' : messageToSend,
            userMessage: messageToSend,
            repository: selectedRepository.value || undefined,
            path: `/claude/${sessionFilename.value}`,
            lastModified: Date.now(),
        };

        // Check if session already exists
        const existingSession = claudeSessions.value.find((s) => s.filename === sessionFilename.value);
        if (!existingSession) {
            claudeSessions.value.unshift(dummySession);
        }
    }

    try {
        const result = await sendMessageToApi(
            {
                prompt: messageToSend,
                sessionId: sessionId.value || undefined, // Don't pass null/empty session ID
                sessionFilename: sessionFilename.value!,
                repositoryPath: selectedRepositoryData.value?.local_path,
                conversationId: conversationId.value || undefined,
            },
            (text, rawResponse) => {
                // Extract session ID from init response
                if (rawResponse && rawResponse.type === 'system' && rawResponse.subtype === 'init' && rawResponse.session_id) {
                    sessionId.value = rawResponse.session_id;
                }

                // Check if this is a system message that should be hidden
                if (hideSystemMessages.value && rawResponse) {
                    // Check if this response contains text content
                    const hasTextContent =
                        (rawResponse.type === 'content' && rawResponse.content) ||
                        (rawResponse.type === 'assistant' && rawResponse.message?.content?.some((item: any) => item.type === 'text'));

                    // Only append if it has text content or hideSystemMessages is false
                    if (!hasTextContent && rawResponse.type !== 'content') {
                        return; // Skip non-text responses when hiding system messages
                    }
                }

                // Create a new assistant message for each response
                const assistantMessage = addAssistantMessage();
                appendToMessage(assistantMessage.id, text, rawResponse);
                scrollToBottom();
            },
        );

        // Store the conversation ID and filename if they were created
        if (result && result.conversationId && !conversationId.value) {
            conversationId.value = result.conversationId;
            console.log('Conversation created with ID:', conversationId.value);
            // Refresh conversations list
            setTimeout(() => {
                fetchConversations();
            }, 500);
        }
        if (result && result.sessionFilename && !sessionFilename.value) {
            sessionFilename.value = result.sessionFilename;
            console.log('Session filename created:', sessionFilename.value);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        // Create an error message if no assistant message exists
        const errorMessage = addAssistantMessage();
        appendToMessage(errorMessage.id, 'Sorry, I encountered an error. Please try again.');
    } finally {
        isLoading.value = false;
        await scrollToBottom();
        // Removed auto-focus after sending message to allow user to select/copy text

        // Redirect to conversation URL if this is a new conversation
        if (!props.sessionFile && !props.conversationId && conversationId.value) {
            const url = `/claude/conversation/${conversationId.value}`;
            router.visit(url);
        }

        // Refresh sessions list to ensure it's up to date
        if (!props.sessionFile) {
            // This was a new session, refresh the list after a short delay
            setTimeout(() => {
                refreshSessions();
            }, 1000);
        }
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

const loadSessionMessages = async (isPolling = false) => {
    if (!props.sessionFile) return;

    try {
        const sessionData = await loadSession(props.sessionFile);

        // Track if there are incomplete conversations
        incompleteMessageFound.value = false;

        if (!isPolling) {
            // Initial load - clear messages and load all
            messages.value = [];

            // Process each conversation
            for (const conversation of sessionData) {
                // Check if conversation is incomplete
                if (!conversation.isComplete) {
                    incompleteMessageFound.value = true;
                }

                // Add user message
                messages.value.push({
                    id: Date.now() + Math.random(),
                    content: conversation.userMessage || '',
                    role: 'user',
                    timestamp: new Date(conversation.timestamp),
                });

                if (conversation.rawJsonResponses?.length) {
                    for (let i = 0; i < conversation.rawJsonResponses.length; i++) {
                        const rawResponse = conversation.rawJsonResponses[i];
                        const content = extractTextFromResponse(rawResponse);

                        // Add all responses, even if they don't have traditional text content
                        // This ensures we see system messages, tool usage, results, etc.
                        messages.value.push({
                            id: Date.now() + Math.random() + i,
                            content: content || `[${rawResponse.type || 'unknown'} response]`,
                            role: 'assistant',
                            timestamp: new Date(conversation.timestamp),
                            rawResponses: [rawResponse],
                        });
                    }
                }
            }
        } else {
            // Polling - only update the last conversation if it was incomplete
            if (sessionData.length > 0) {
                const lastConversation = sessionData[sessionData.length - 1];

                // Check if the last conversation is complete now
                if (!lastConversation.isComplete) {
                    incompleteMessageFound.value = true;
                }

                // Find the last assistant message index
                let lastAssistantIndex = -1;
                for (let i = messages.value.length - 1; i >= 0; i--) {
                    if (messages.value[i].role === 'assistant') {
                        lastAssistantIndex = i;
                        break;
                    }
                }

                // Update the last assistant message(s) if the conversation has more responses
                if (lastAssistantIndex >= 0 && lastConversation.rawJsonResponses?.length) {
                    // Count existing assistant messages for this conversation
                    let existingResponseCount = 0;
                    for (let i = lastAssistantIndex; i >= 0 && messages.value[i].role === 'assistant'; i--) {
                        existingResponseCount++;
                    }

                    // Add new responses if there are more than we already have
                    if (lastConversation.rawJsonResponses.length > existingResponseCount) {
                        for (let i = existingResponseCount; i < lastConversation.rawJsonResponses.length; i++) {
                            const rawResponse = lastConversation.rawJsonResponses[i];
                            const content = extractTextFromResponse(rawResponse);

                            messages.value.push({
                                id: Date.now() + Math.random() + i,
                                content: content || `[${rawResponse.type || 'unknown'} response]`,
                                role: 'assistant',
                                timestamp: new Date(lastConversation.timestamp),
                                rawResponses: [rawResponse],
                            });
                        }
                    }
                }
            }
        }

        // Set session info
        sessionFilename.value = props.sessionFile;
        if (sessionData.length > 0) {
            // Find the actual Claude session ID from the raw responses
            const lastConversation = sessionData[sessionData.length - 1];
            if (lastConversation.rawJsonResponses && lastConversation.rawJsonResponses.length > 0) {
                for (const response of lastConversation.rawJsonResponses) {
                    if (response.type === 'system' && response.subtype === 'init' && response.session_id) {
                        sessionId.value = response.session_id;
                        break;
                    }
                }
            }

            // Set repository from session data if available, but only if not already set from props
            // Check all conversations to find one with a repository path
            if (!selectedRepository.value) {
                for (const conversation of sessionData) {
                    if (conversation.repositoryPath) {
                        // Extract repository name from path
                        const pathParts = conversation.repositoryPath.split('/');
                        const repoName = pathParts[pathParts.length - 1];
                        selectedRepository.value = repoName;
                        break; // Use the first repository found
                    }
                }
            }
        }

        // Wait for DOM to update with all messages before scrolling
        await nextTick();
        // Add a delay to ensure all messages are fully rendered
        setTimeout(async () => {
            await scrollToBottom();
            // Try scrolling again after another delay
            setTimeout(() => {
                scrollToBottom();
            }, 200);
        }, 150);

        // Start polling if there are incomplete messages
        if (incompleteMessageFound.value && !pollingInterval.value) {
            startPolling();
        } else if (!incompleteMessageFound.value && pollingInterval.value) {
            // Stop polling if all messages are complete
            stopPolling();
        }
    } catch (error) {
        console.error('Error loading session messages:', error);
    }
};

const startPolling = () => {
    if (pollingInterval.value) return;

    console.log('Starting polling for session updates...');
    pollingInterval.value = window.setInterval(() => {
        loadSessionMessages(true);
    }, 2000); // Poll every 2 seconds
};

const stopPolling = () => {
    if (pollingInterval.value) {
        console.log('Stopping polling for session updates...');
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// Watch for changes in sessionFile prop
watch(
    () => props.sessionFile,
    async (newFile, oldFile) => {
        if (newFile !== oldFile) {
            // Stop any existing polling
            stopPolling();
            // Reset tracking variables
            lastMessageCount.value = 0;
            incompleteMessageFound.value = false;
            // Clear existing messages when switching sessions or when no session is specified
            messages.value = [];

            if (newFile) {
                await loadSessionMessages();
            } else {
                // No session file specified - reset session state
                sessionFilename.value = null;
                sessionId.value = null;
            }
        }
    },
);

// Watch for changes in repository prop
watch(
    () => props.repository,
    (newRepo) => {
        if (newRepo) {
            selectedRepository.value = newRepo;
        } else {
            // No repository specified - clear the selection
            selectedRepository.value = null;
        }
    },
    { immediate: true },
);

// Track polling state
let pollInterval: NodeJS.Timeout | null = null;

// Load messages from database for job-based conversations
const loadConversationMessages = async () => {
    if (!props.conversationId) return;

    try {
        const response = await fetch(`/api/conversations/${props.conversationId}/messages`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            }
        });
        const data = await response.json();

        console.log('Loading conversation messages:', data);

        messages.value = [];
        let hasStreamingMessage = false;

        for (const msg of data.messages) {
            if (msg.role === 'user') {
                const userMessage = addUserMessage(msg.content || '');
                userMessage.timestamp = new Date(msg.created_at);
            } else if (msg.role === 'assistant') {
                const assistantMessage = addAssistantMessage();
                // Directly set the content instead of using appendToMessage
                // since we don't have raw responses from the database
                if (msg.content) {
                    assistantMessage.content = msg.content;
                }
                assistantMessage.timestamp = new Date(msg.created_at);

                if (msg.is_streaming) {
                    hasStreamingMessage = true;
                }
            }
        }

        console.log('Messages after loading:', messages.value);
        console.log('Filtered messages:', filteredMessages.value);

        // Start polling if there are streaming messages
        if (hasStreamingMessage) {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(loadConversationMessages, 1000);
        } else if (data.messages.length > 0) {
            // Poll less frequently for updates
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(loadConversationMessages, 5000);
        }

        await scrollToBottom();
    } catch (error) {
        console.error('Error loading conversation messages:', error);
    }
};

onMounted(async () => {
    // Fetch repositories first to ensure they're available
    await fetchRepositories();

    // Initialize from props if we have a conversation
    if (props.conversationId) {
        conversationId.value = props.conversationId;
    }

    if (props.sessionId) {
        sessionId.value = props.sessionId;
    }

    if (props.sessionFile) {
        // Load from session file
        await loadSessionMessages();
    } else if (props.conversationId) {
        // Load from database for job-based conversations
        await loadConversationMessages();
    } else {
        // New conversation - clear everything
        messages.value = [];
        sessionFilename.value = null;
        sessionId.value = null;
        // Only focus on initial mount for new sessions
        focusInput(false);
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button
                @click="hideSystemMessages = !hideSystemMessages"
                variant="ghost"
                size="icon"
                :title="hideSystemMessages ? 'Show System Messages' : 'Hide System Messages'"
                class="mr-2"
            >
                <component :is="hideSystemMessages ? EyeOff : Eye" class="h-4 w-4" />
            </Button>
        </template>
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-gray-50 dark:bg-gray-900">
            <!-- Chat Messages -->
            <ScrollArea ref="messagesContainer" class="flex-1 p-4">
                <div class="space-y-4">
                    <ChatMessage
                        v-for="message in filteredMessages"
                        :key="message.id"
                        :message="message"
                        :format-time="formatTime"
                        :show-raw-responses="false"
                    />

                    <div v-if="isLoading" class="flex justify-start">
                        <div class="max-w-[70%] rounded-2xl bg-white px-4 py-2 shadow-sm dark:bg-gray-800">
                            <div class="flex space-x-1">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.3s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.15s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <!-- Input Area -->
            <div class="border-t bg-white p-4 dark:bg-gray-800">
                <div>
                    <div v-if="selectedRepository" class="mb-2 flex items-center text-sm text-muted-foreground">
                        <GitBranch class="mr-1 h-3 w-3" />
                        Working in:
                        <span class="ml-1 font-medium">{{ selectedRepositoryData ? selectedRepositoryData.name : selectedRepository }}</span>
                        <span v-if="!selectedRepositoryData && repositories.length > 0" class="ml-2 text-xs text-yellow-600">(not found)</span>
                    </div>
                    <div class="flex items-end space-x-2">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            @input="adjustTextareaHeight"
                            placeholder="Type a message..."
                            class="max-h-[120px] min-h-[40px] resize-none overflow-y-auto text-sm"
                            :rows="1"
                            :disabled="isLoading"
                        />
                        <Button @click="sendMessage" :disabled="!inputMessage.trim() || isLoading" size="icon" class="h-10 w-10 rounded-full">
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
