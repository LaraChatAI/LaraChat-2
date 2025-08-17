<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import EnvFileModal from '@/components/EnvFileModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Activity, ArrowRight, FileCode, FileKey2, MessageSquare, Send, Sparkles } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    repository: {
        id: number;
        name: string;
        url: string;
        branch?: string;
        path: string;
        has_hot_folder: boolean;
        created_at: string;
        updated_at: string;
    };
    stats?: {
        files_count: number;
        directories_count: number;
        total_size: string;
        last_commit?: string;
    };
    recent_conversations?: Array<{
        id: number;
        title: string;
        created_at: string;
    }>;
}>();

const messageInput = ref('');
const showEnvModal = ref(false);

const startChatWithMessage = (message?: string) => {
    const finalMessage = message || messageInput.value.trim();
    if (finalMessage) {
        // Use router.get with data to properly send parameters
        router.get('/claude/new', {
            message: finalMessage,
            repository: props.repository.name,
        });
    }
};

const quickMessages = [
    { text: 'Show this week tasks', icon: '📋' },
    { text: 'Let me ask you about', icon: '💬' },
    { text: 'Review recent changes', icon: '🔍' },
    { text: 'Help me debug an issue', icon: '🐛' },
];
</script>

<template>
    <AppLayout>
        <template #header-actions>
            <Button
                @click="showEnvModal = true"
                variant="outline"
                size="sm"
            >
                <FileKey2 class="mr-2 h-4 w-4" />
                Environment
            </Button>
        </template>
        
        <div class="container mx-auto py-6">
            <!-- Main CTA Section -->
            <div class="flex min-h-[60vh] flex-col items-center justify-center">
                <!-- Conversation Starter -->
                <div class="w-full max-w-2xl space-y-6">
                    <div class="text-center">
                        <div class="mb-2 flex items-center justify-center">
                            <Sparkles class="h-8 w-8 text-primary" />
                        </div>
                        <h2 class="text-2xl font-semibold">Start a conversation</h2>
                        <p class="mt-2 text-muted-foreground">Ask Claude about your {{ repository.name }} codebase</p>
                    </div>

                    <!-- Main Input -->
                    <div class="space-y-2">
                        <div class="relative">
                            <Textarea
                                v-model="messageInput"
                                placeholder="Type your message or question..."
                                @keydown.meta.enter.prevent="startChatWithMessage()"
                                @keydown.alt.enter.prevent="startChatWithMessage()"
                                class="min-h-[100px] resize-y pr-14 pl-5 text-base"
                            />
                            <Button
                                @click="startChatWithMessage()"
                                :disabled="!messageInput.trim()"
                                size="icon"
                                class="absolute bottom-2 right-2 h-10 w-10 rounded-full"
                            >
                                <Send class="h-4 w-4" />
                            </Button>
                        </div>
                        <p class="text-xs text-muted-foreground text-center">Press Cmd+Enter (Mac) or Alt+Enter (Windows) to send</p>
                    </div>

                    <!-- Quick Messages -->
                    <div class="space-y-3">
                        <p class="text-center text-sm text-muted-foreground">Or start with:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <Button
                                v-for="(message, index) in quickMessages"
                                :key="index"
                                @click="startChatWithMessage(message.text)"
                                variant="outline"
                                size="sm"
                                class="group"
                            >
                                <span class="mr-2">{{ message.icon }}</span>
                                {{ message.text }}
                                <ArrowRight class="ml-2 h-3 w-3 transition-transform group-hover:translate-x-0.5" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Minimal Stats -->
                <div class="mt-12 flex items-center gap-8 text-sm text-muted-foreground" v-if="stats">
                    <div class="flex items-center gap-2">
                        <FileCode class="h-4 w-4" />
                        <span>{{ stats.files_count }} files</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <MessageSquare class="h-4 w-4" />
                        <span>{{ recent_conversations?.length || 0 }} conversations</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Activity class="h-4 w-4" />
                        <span>{{ stats.total_size }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Environment Variables Modal -->
        <EnvFileModal
            v-model="showEnvModal"
            :repository-id="repository.id"
        />
    </AppLayout>
</template>
