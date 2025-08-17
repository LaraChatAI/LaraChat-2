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
import axios from 'axios';
import { Archive, ArchiveRestore, Eye, EyeOff, ExternalLink, GitBranch, Send } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

// Constants
const POLLING_INTERVAL_MS = 2000;
const SCROLL_DELAY_MS = 150;
const SCROLL_RETRY_DELAY_MS = 200;
const SESSION_REFRESH_DELAY_MS = 1000;

const props = defineProps<{
    sessionFile?: string;
    repository?: string;
    conversationId?: number;
    sessionId?: string;
    isArchived?: boolean;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const items: BreadcrumbItem[] = [{ title: 'Claude', href: '/claude' }];
    if (selectedRepository.value && selectedRepositoryData.value) {
        items.push({
            title: selectedRepositoryData.value.name,
            icon: GitBranch,
        });
    } else if (selectedRepository.value) {
        items.push({
            title: selectedRepository.value,
            icon: GitBranch,
        });
    }
    return items;
});

// Composables
const { messagesContainer, textareaRef, scrollToBottom, isAtBottom, adjustTextareaHeight, resetTextareaHeight, focusInput, setupFocusHandlers } =
    useChatUI();
const { messages, addUserMessage, addAssistantMessage, appendToMessage, formatTime } = useChatMessages();
const { isLoading, sendMessageToApi, loadSession } = useClaudeApi();
const { claudeSessions, refreshSessions } = useClaudeSessions();
const { conversations, fetchConversations, startPolling: startConversationPolling, stopPolling: stopConversationPolling } = useConversations();
const { repositories, fetchRepositories } = useRepositories();

// Local state
const inputMessage = ref('');
const sessionFilename = ref<string | null>(props.sessionFile || null);
const sessionId = ref<string | null>(props.sessionId || null);
const conversationId = ref<number | null>(props.conversationId || null);
const conversation = ref<any>(null);
const hideSystemMessages = ref(true);
const selectedRepository = ref<string | null>(props.repository || null);
const isUserInteracting = ref(false);
const pendingUpdates = ref<any[]>([]);
const isArchived = ref(props.isArchived || false);
const isArchiving = ref(false);
const showArchiveConfirm = ref(false);

// Polling state
const pollingInterval = ref<number | null>(null);
const incompleteMessageFound = ref(false);

// Track user interaction
const handleUserInteractionStart = () => {
    isUserInteracting.value = true;
};

const handleUserInteractionEnd = () => {
    isUserInteracting.value = false;
    // Apply pending updates if any
    if (pendingUpdates.value.length > 0) {
        applyPendingUpdates();
    }
};

const applyPendingUpdates = () => {
    if (pendingUpdates.value.length === 0) return;

    const updates = [...pendingUpdates.value];
    pendingUpdates.value = [];

    // Apply all pending updates
    updates.forEach((update) => {
        if (update.type === 'messages') {
            messages.value = update.data;
        } else if (update.type === 'append') {
            messages.value.push(...update.data);
        }
    });

    // Scroll only if user is at bottom
    if (isAtBottom.value) {
        nextTick(() => scrollToBottom(false));
    }
};

// Setup focus handlers
setupFocusHandlers(isLoading);

// Computed properties
const selectedRepositoryData = computed(() => {
    if (!selectedRepository.value) return null;
    return repositories.value.find((r) => {
        if (r.local_path?.endsWith('/' + selectedRepository.value)) return true;
        return r.name.toLowerCase() === selectedRepository.value?.toLowerCase();
    });
});

const filteredMessages = computed(() => {
    if (!hideSystemMessages.value) return messages.value;

    return messages.value.filter((message) => {
        if (message.role === 'user') return true;

        if (message.rawResponses?.length > 0) {
            return message.rawResponses.some((response) => {
                if (response.type === 'assistant' && response.message?.content) {
                    return response.message.content.some((item: any) => item.type === 'text');
                }
                return false;
            });
        }
        return true;
    });
});

// Utility functions
const generateSessionFilename = () => {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
    const tempId = Date.now().toString(36);
    return `${timestamp}-session-${tempId}.json`;
};

const extractSessionId = (responses: any[]) => {
    for (const response of responses) {
        if (response.type === 'system' && response.subtype === 'init' && response.session_id) {
            return response.session_id;
        }
    }
    return null;
};

const extractRepositoryFromPath = (path: string) => {
    const pathParts = path.split('/');
    return pathParts[pathParts.length - 1];
};

const delayedScroll = async (force = false) => {
    await nextTick();
    setTimeout(async () => {
        await scrollToBottom(force);
        setTimeout(() => scrollToBottom(force), SCROLL_RETRY_DELAY_MS);
    }, SCROLL_DELAY_MS);
};

// Polling management
const startPolling = (interval = POLLING_INTERVAL_MS) => {
    stopPolling();
    pollingInterval.value = window.setInterval(() => {
        if (props.sessionFile) {
            loadSessionMessages(true);
        }
    }, interval);
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// Message handling
const processConversationResponses = (conversation: any, isPolling = false) => {
    const messagesList = [];

    // Add user message
    if (!isPolling || messages.value.length === 0) {
        messagesList.push({
            id: Date.now() + Math.random(),
            content: conversation.userMessage || '',
            role: 'user',
            timestamp: new Date(conversation.timestamp),
        });
    }

    // Process any responses regardless of role field
    if (conversation.rawJsonResponses?.length) {
        // Handle rawJsonResponses as an array of strings (JSON strings that need parsing)
        conversation.rawJsonResponses.forEach((rawResponseStr: any, i: number) => {
            let rawResponse: any;

            // Parse if it's a string, otherwise use as-is
            if (typeof rawResponseStr === 'string') {
                try {
                    rawResponse = JSON.parse(rawResponseStr);
                } catch (e) {
                    console.error('Failed to parse raw response:', e);
                    rawResponse = { type: 'error', content: rawResponseStr };
                }
            } else {
                rawResponse = rawResponseStr;
            }

            const content = extractTextFromResponse(rawResponse);
            messagesList.push({
                id: Date.now() + Math.random() + i,
                content: content || `[${rawResponse.type || 'unknown'} response]`,
                role: 'assistant',
                timestamp: new Date(conversation.timestamp),
                rawResponses: [rawResponse],
            });
        });
    }

    return messagesList;
};

const loadSessionMessages = async (isPolling = false) => {
    if (!props.sessionFile) return;

    try {
        const sessionData = await loadSession(props.sessionFile);
        incompleteMessageFound.value = false;

        if (!isPolling) {
            const newMessages = [];

            for (const conversation of sessionData) {
                if (!conversation.isComplete) {
                    incompleteMessageFound.value = true;
                }

                // Process entries with role field (new format)
                if (conversation.role === 'user') {
                    const processedMessages = processConversationResponses(conversation);
                    newMessages.push(...processedMessages);
                } else {
                    // For entries without role or non-user entries
                    const messagesList = [];

                    // Add user message from this conversation entry
                    if (conversation.userMessage) {
                        messagesList.push({
                            id: Date.now() + Math.random(),
                            content: conversation.userMessage || '',
                            role: 'user',
                            timestamp: new Date(conversation.timestamp),
                        });
                    }

                    // Add assistant responses
                    if (conversation.rawJsonResponses?.length) {
                        conversation.rawJsonResponses.forEach((rawResponseStr: any, i: number) => {
                            let rawResponse: any;
                            if (typeof rawResponseStr === 'string') {
                                try {
                                    rawResponse = JSON.parse(rawResponseStr);
                                } catch (e) {
                                    console.error('Failed to parse raw response:', e);
                                    rawResponse = { type: 'error', content: rawResponseStr };
                                }
                            } else {
                                rawResponse = rawResponseStr;
                            }

                            const content = extractTextFromResponse(rawResponse);
                            messagesList.push({
                                id: Date.now() + Math.random() + i,
                                content: content || `[${rawResponse.type || 'unknown'} response]`,
                                role: 'assistant',
                                timestamp: new Date(conversation.timestamp),
                                rawResponses: [rawResponse],
                            });
                        });
                    }

                    newMessages.push(...messagesList);
                }
            }

            // Update messages - defer if user is interacting
            if (isUserInteracting.value) {
                pendingUpdates.value = [{ type: 'messages', data: newMessages }];
            } else {
                messages.value = newMessages;
            }

            // Extract session metadata
            if (sessionData.length > 0) {
                const lastConversation = sessionData[sessionData.length - 1];
                const extractedSessionId = extractSessionId(lastConversation.rawJsonResponses || []);
                if (extractedSessionId) sessionId.value = extractedSessionId;

                if (!selectedRepository.value) {
                    for (const conversation of sessionData) {
                        if (conversation.repositoryPath) {
                            selectedRepository.value = extractRepositoryFromPath(conversation.repositoryPath);
                            break;
                        }
                    }
                }
            }
        } else {
            // Handle polling updates for conversations
            if (sessionData.length > 0) {
                const lastConversation = sessionData[sessionData.length - 1];
                
                // Check if conversation is incomplete for continued polling
                if (!lastConversation.isComplete) {
                    incompleteMessageFound.value = true;
                }

                // Count existing messages from ALL conversations, not just assistant messages
                let totalExistingMessages = 0;
                for (let i = 0; i < sessionData.length - 1; i++) {
                    const conv = sessionData[i];
                    // Count user message
                    if (conv.userMessage) totalExistingMessages++;
                    // Count responses
                    totalExistingMessages += (conv.rawJsonResponses?.length || 0);
                }
                
                // For the last conversation, check how many messages we already have
                const lastConvUserMessageShown = messages.value.some(m => 
                    m.role === 'user' && m.content === lastConversation.userMessage
                );
                if (!lastConvUserMessageShown && lastConversation.userMessage) {
                    // Add the user message if not shown
                    messages.value.push({
                        id: Date.now() + Math.random(),
                        content: lastConversation.userMessage,
                        role: 'user',
                        timestamp: new Date(lastConversation.timestamp),
                    });
                }
                
                // Calculate how many assistant messages from this conversation we already have
                // Start counting from after all previous conversations' messages
                const lastConvStartIndex = totalExistingMessages + (lastConvUserMessageShown ? 0 : 1);
                const currentConvAssistantCount = messages.value.slice(lastConvStartIndex).filter(m => 
                    m.role === 'assistant'
                ).length;
                
                // Get new responses from this position
                const newResponses = lastConversation.rawJsonResponses?.slice(currentConvAssistantCount) || [];

                // Process any new responses
                if (newResponses.length > 0) {
                    newResponses.forEach((rawResponseStr: any, i: number) => {
                        let rawResponse: any;
                        
                        // Parse if it's a string, otherwise use as-is
                        if (typeof rawResponseStr === 'string') {
                            try {
                                rawResponse = JSON.parse(rawResponseStr);
                            } catch (e) {
                                console.error('Failed to parse raw response:', e);
                                rawResponse = { type: 'error', content: rawResponseStr };
                            }
                        } else {
                            rawResponse = rawResponseStr;
                        }
                        
                        const content = extractTextFromResponse(rawResponse);
                        const newMessage = {
                            id: Date.now() + Math.random() + i,
                            content: content || `[${rawResponse.type || 'unknown'} response]`,
                            role: 'assistant',
                            timestamp: new Date(lastConversation.timestamp),
                            rawResponses: [rawResponse],
                        };
                        
                        // Add or queue the message
                        if (isUserInteracting.value) {
                            pendingUpdates.value.push({ type: 'append', data: [newMessage] });
                        } else {
                            messages.value.push(newMessage);
                        }
                    });
                }
            }
        }

        sessionFilename.value = props.sessionFile;

        // Only scroll if not interacting
        if (!isUserInteracting.value) {
            // Force scroll to bottom on initial load, otherwise use smart scrolling
            await delayedScroll(!isPolling);
        }

        // Manage polling based on completion status
        if (incompleteMessageFound.value && !pollingInterval.value) {
            startPolling();
        } else if (!incompleteMessageFound.value && pollingInterval.value) {
            stopPolling();
        } else if (pollingInterval.value && messages.value.length > 0) {
            // If we were polling rapidly for file creation and now have messages,
            // switch to normal polling speed
            stopPolling();
            if (incompleteMessageFound.value) {
                startPolling(POLLING_INTERVAL_MS);
            }
        }
    } catch (error: any) {
        console.error('Error loading session messages:', error);

        // If the session file doesn't exist yet (404), start polling to retry
        if (error?.response?.status === 404) {
            console.log('Session file not found yet, starting polling to retry...');

            // Show loading state while waiting for session file
            if (messages.value.length === 0) {
                isLoading.value = true;
            }

            // Keep trying to load the session file
            if (!pollingInterval.value) {
                startPolling(500); // Poll more frequently when waiting for file
            }
        }
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isLoading.value || conversation.value?.is_processing) return;

    const messageToSend = inputMessage.value;
    addUserMessage(messageToSend);
    inputMessage.value = '';
    resetTextareaHeight();
    isLoading.value = true;
    await scrollToBottom(true); // Force scroll when user sends a message

    // Initialize session if needed
    if (!sessionFilename.value) {
        sessionFilename.value = props.sessionFile || generateSessionFilename();

        // Add to sessions list immediately for new sessions
        if (!props.sessionFile) {
            const existingSession = claudeSessions.value.find((s) => s.filename === sessionFilename.value);
            if (!existingSession) {
                claudeSessions.value.unshift({
                    filename: sessionFilename.value,
                    name: messageToSend.substring(0, 30) + (messageToSend.length > 30 ? '...' : ''),
                    userMessage: messageToSend,
                    repository: selectedRepository.value || undefined,
                    path: `/claude/${sessionFilename.value}`,
                    lastModified: Date.now(),
                });
            }
        }
    }

    try {
        const result = await sendMessageToApi(
            {
                prompt: messageToSend,
                sessionId: sessionId.value || undefined,
                sessionFilename: sessionFilename.value,
                repositoryPath: selectedRepositoryData.value?.local_path,
                conversationId: conversationId.value || undefined,
            },
            (text, rawResponse) => {
                // Extract session ID from init response
                if (rawResponse?.type === 'system' && rawResponse.subtype === 'init' && rawResponse.session_id) {
                    sessionId.value = rawResponse.session_id;
                }

                // Filter system messages if needed
                if (hideSystemMessages.value && rawResponse) {
                    const hasTextContent =
                        (rawResponse.type === 'content' && rawResponse.content) ||
                        (rawResponse.type === 'assistant' && rawResponse.message?.content?.some((item: any) => item.type === 'text'));

                    if (!hasTextContent && rawResponse.type !== 'content') return;
                }

                const assistantMessage = addAssistantMessage();
                appendToMessage(assistantMessage.id, text, rawResponse);

                // Only scroll if user is not selecting text and is at bottom
                if (!isUserInteracting.value && isAtBottom.value) {
                    scrollToBottom(false);
                }
            },
        );

        // Handle result
        if (result?.conversationId) {
            if (!conversationId.value) {
                conversationId.value = result.conversationId;
                // Update the URL immediately without losing state
                if (!props.conversationId) {
                    const targetPath = `/claude/conversation/${conversationId.value}`;
                    window.history.replaceState({}, '', targetPath);
                }
            }
            
            // Immediately refresh conversations to show in sidebar
            await fetchConversations(true, true); // Force refresh silently
            
            // Start temporary conversation polling to ensure sidebar updates
            startConversationPolling(1000); // Poll every 1 second temporarily
            setTimeout(() => stopConversationPolling(), 5000); // Stop after 5 seconds
        }
        if (result?.sessionFilename && !sessionFilename.value) {
            sessionFilename.value = result.sessionFilename;
        }
        
        // Always start polling after sending a message to get server updates
        startPolling(POLLING_INTERVAL_MS);
    } catch (error) {
        console.error('Error sending message:', error);
        const errorMessage = addAssistantMessage();
        appendToMessage(errorMessage.id, 'Sorry, I encountered an error. Please try again.');
    } finally {
        isLoading.value = false;
        await scrollToBottom(false); // Smart scroll after message completes

        if (!props.sessionFile) {
            setTimeout(() => {
                refreshSessions();
                // Also refresh conversations
                fetchConversations(true, true);
            }, SESSION_REFRESH_DELAY_MS);
        }
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (!isLoading.value && !conversation.value?.is_processing) {
            sendMessage();
        }
    }
};

const archiveConversation = async () => {
    if (!conversationId.value || isArchiving.value) return;

    isArchiving.value = true;
    try {
        // Find the current index and next conversation BEFORE archiving
        const currentIndex = conversations.value.findIndex(c => c.id === conversationId.value);
        let nextConversationId: number | null = null;
        
        // Determine which conversation to navigate to after archiving
        if (currentIndex !== -1) {
            if (currentIndex < conversations.value.length - 1) {
                // There's a conversation below (at the same position after removal)
                nextConversationId = conversations.value[currentIndex + 1].id;
            } else if (currentIndex > 0) {
                // No conversation below, but there's one above
                nextConversationId = conversations.value[currentIndex - 1].id;
            }
        }

        // Archive the conversation
        await axios.post(`/api/conversations/${conversationId.value}/archive`);
        isArchived.value = true;
        showArchiveConfirm.value = false;

        // Refresh conversations list to update sidebar
        await fetchConversations(false, true);

        // Navigate to the determined conversation
        if (nextConversationId) {
            router.visit(`/claude/conversation/${nextConversationId}`);
        } else {
            // No other conversations, go to main page
            router.visit('/claude');
        }
    } catch (error) {
        console.error('Error archiving conversation:', error);
    } finally {
        isArchiving.value = false;
    }
};

const unarchiveConversation = async () => {
    if (!conversationId.value || isArchiving.value) return;

    isArchiving.value = true;
    try {
        await axios.post(`/api/conversations/${conversationId.value}/unarchive`);
        isArchived.value = false;

        // Refresh conversations list to update sidebar
        await fetchConversations(false, true);
    } catch (error) {
        console.error('Error unarchiving conversation:', error);
    } finally {
        isArchiving.value = false;
    }
};

const openPreview = () => {
    if (!conversationId.value) return;
    
    const conversation = conversations.value.find(c => c.id === conversationId.value);
    if (!conversation?.project_directory) return;
    
    const subdomain = conversation.project_directory.split('/').pop();
    const url = `https://${subdomain}.larachat-restricted.coding.cab`;
    window.open(url, '_blank');
};

// Watchers
watch(
    () => props.sessionFile,
    async (newFile, oldFile) => {
        if (newFile !== oldFile) {
            stopPolling();
            incompleteMessageFound.value = false;
            messages.value = [];

            if (newFile) {
                await loadSessionMessages();
                // Force scroll to bottom when switching to a different conversation
                await scrollToBottom(true);
            } else {
                sessionFilename.value = null;
                sessionId.value = null;
            }
        }
    },
);

watch(
    () => props.repository,
    (newRepo) => {
        selectedRepository.value = newRepo || null;
    },
    { immediate: true },
);

// Reset archive state when conversation changes
watch(
    () => props.conversationId,
    async (newId) => {
        conversationId.value = newId || null;
        // Fetch the conversation when ID changes
        if (newId) {
            await fetchConversation(newId);
        } else {
            conversation.value = null;
        }
        // Reset archive button state when conversation changes
        isArchived.value = props.isArchived || false;
        isArchiving.value = false;
        showArchiveConfirm.value = false;
    },
);

// Update archive state when prop changes
watch(
    () => props.isArchived,
    (newValue) => {
        isArchived.value = newValue || false;
        showArchiveConfirm.value = false;
    },
);

// Update conversation when conversations list changes
watch(
    conversations,
    () => {
        if (conversationId.value) {
            const updatedConv = conversations.value.find(c => c.id === conversationId.value);
            if (updatedConv) {
                conversation.value = updatedConv;
            }
        }
    },
    { deep: true }
);

// Function to fetch a specific conversation
const fetchConversation = async (id: number) => {
    try {
        // First try to find it in the conversations list
        const foundConv = conversations.value.find(c => c.id === id);
        if (foundConv) {
            conversation.value = foundConv;
        } else {
            // If not found, fetch conversations and try again
            await fetchConversations(true, true);
            const foundConvAfterFetch = conversations.value.find(c => c.id === id);
            if (foundConvAfterFetch) {
                conversation.value = foundConvAfterFetch;
            }
        }
    } catch (error) {
        console.error('Error fetching conversation:', error);
    }
};

// Lifecycle
onMounted(async () => {
    // Set up global selection tracking
    let selectionTimer: number | null = null;

    const handleSelectionChange = () => {
        const selection = window.getSelection();
        if (selection && selection.toString().length > 0) {
            isUserInteracting.value = true;
            if (selectionTimer) clearTimeout(selectionTimer);
        } else {
            // Delay ending interaction to avoid flicker
            if (selectionTimer) clearTimeout(selectionTimer);
            selectionTimer = window.setTimeout(() => {
                handleUserInteractionEnd();
            }, 100);
        }
    };

    document.addEventListener('selectionchange', handleSelectionChange);
    document.addEventListener('mousedown', handleUserInteractionStart);
    document.addEventListener('mouseup', () => {
        // Delay to allow selection to complete
        setTimeout(handleSelectionChange, 50);
    });

    await fetchRepositories();
    // Fetch conversations on mount to ensure they're up to date
    await fetchConversations(false, true);

    if (props.conversationId) {
        conversationId.value = props.conversationId;
        // Fetch the specific conversation
        await fetchConversation(props.conversationId);
    }
    if (props.sessionId) sessionId.value = props.sessionId;

    if (props.sessionFile) {
        await loadSessionMessages();
        // Force scroll to bottom when opening an existing conversation
        await scrollToBottom(true);
        // Also refresh conversations to ensure sidebar is up to date
        await fetchConversations(true, true);
    } else if (props.conversationId) {
        // For conversation-based pages, we'll need to wait for the session file
        // Start polling immediately to check for session file
        if (!pollingInterval.value) {
            startPolling(POLLING_INTERVAL_MS);
        }
        // Also refresh conversations to ensure sidebar is up to date
        await fetchConversations(true, true);
    } else {
        messages.value = [];
        sessionFilename.value = null;
        sessionId.value = null;
        focusInput(false);
    }
});

onUnmounted(() => {
    stopPolling();
    // Clean up event listeners
    document.removeEventListener('selectionchange', () => {});
    document.removeEventListener('mousedown', handleUserInteractionStart);
    document.removeEventListener('mouseup', () => {});
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button
                v-if="conversationId && conversations.find(c => c.id === conversationId)?.project_directory"
                @click="openPreview"
                variant="ghost"
                size="icon"
                :title="`Preview ${conversations.find(c => c.id === conversationId)?.project_directory.split('/').pop()}`"
                class="mr-2"
            >
                <ExternalLink class="h-4 w-4" />
            </Button>
            <Button
                v-if="conversationId && !showArchiveConfirm"
                @click="isArchived ? unarchiveConversation() : (showArchiveConfirm = true)"
                variant="ghost"
                size="icon"
                :title="isArchived ? 'Unarchive Conversation' : 'Archive Conversation'"
                :disabled="isArchiving"
                class="mr-2"
            >
                <component :is="isArchived ? ArchiveRestore : Archive" class="h-4 w-4" />
            </Button>
            <div v-if="conversationId && showArchiveConfirm && !isArchived" class="mr-2 flex gap-2">
                <Button
                    @click="archiveConversation()"
                    variant="destructive"
                    size="sm"
                    :disabled="isArchiving"
                >
                    Confirm Archive
                </Button>
            </div>
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
        <div class="flex h-[calc(100dvh-4rem)] flex-col bg-background">
            <!-- Chat Messages -->
            <ScrollArea ref="messagesContainer" class="flex-1 p-4">
                <div class="space-y-2">
                    <ChatMessage
                        v-for="message in filteredMessages"
                        :key="message.id"
                        :message="message"
                        :format-time="formatTime"
                        :show-raw-responses="false"
                    />

                    <div v-if="isLoading || conversation?.is_processing" class="flex justify-start">
                        <div class="max-w-full sm:max-w-[70%] rounded-2xl bg-card px-4 py-2 shadow-sm">
                            <div class="flex space-x-1">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50 [animation-delay:-0.3s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50 [animation-delay:-0.15s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-muted-foreground/50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <!-- Input Area -->
            <div class="border-t bg-background p-4">
                <div v-if="isArchived" class="text-center text-muted-foreground">
                    This conversation is archived. Unarchive it to continue the conversation.
                </div>
                <div v-else>
                    <div class="flex items-end space-x-2">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            @input="adjustTextareaHeight"
                            placeholder="Type a message..."
                            class="max-h-[120px] min-h-[40px] resize-none overflow-y-auto text-sm"
                            :rows="1"
                            :disabled="isLoading || conversation?.is_processing"
                        />
                        <Button @click="sendMessage" :disabled="!inputMessage.trim() || isLoading || conversation?.is_processing" size="icon" class="h-10 w-10 rounded-full">
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
