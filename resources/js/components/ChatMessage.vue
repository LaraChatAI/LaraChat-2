<script setup lang="ts">
import type { Message } from '@/types/claude';
import { parseMessageContent } from '@/utils/urlParser';
import { computed, ref } from 'vue';

const props = defineProps<{
    message: Message;
    formatTime: (date: Date) => string;
    showRawResponses: boolean;
}>();

const showLocalRawResponse = ref(false);

const parsedContent = computed(() => {
    return parseMessageContent(props.message.content);
});

const getContentType = (message: Message): string | null => {
    if (message.role !== 'assistant' || !message.rawResponses || message.rawResponses.length === 0) {
        return null;
    }

    const firstResponse = message.rawResponses[0];
    if (firstResponse?.message?.content && Array.isArray(firstResponse.message.content)) {
        const contentTypes = firstResponse.message.content.map((item: any) => item.type);
        return contentTypes.join(', ');
    }

    return null;
};

const toggleRawResponse = () => {
    showLocalRawResponse.value = !showLocalRawResponse.value;
};
</script>

<template>
    <div :class="['flex', message.role === 'user' ? 'justify-end' : 'justify-start']">
        <div
            :class="[
                'max-w-full sm:max-w-[70%] rounded-2xl px-4 py-2',
                message.role === 'user' ? 'bg-muted text-foreground' : 'border bg-card text-card-foreground',
            ]"
        >
            <p class="text-sm break-words whitespace-pre-wrap" v-html="parsedContent"></p>
            <div class="mt-1 flex items-center justify-between">
                <p :class="['text-[11px]', message.role === 'user' ? 'text-muted-foreground' : 'text-muted-foreground']">
                    {{ formatTime(message.timestamp) }}
                </p>
                <button
                    v-if="getContentType(message)"
                    @click="toggleRawResponse"
                    :class="[
                        'cursor-pointer text-[11px] hover:underline',
                        message.role === 'user' ? 'text-muted-foreground' : 'text-muted-foreground',
                    ]"
                    type="button"
                >
                    {{ getContentType(message) }}
                </button>
            </div>
            <!-- Raw JSON Responses Display -->
            <div
                v-if="
                    (showRawResponses || showLocalRawResponse) &&
                    message.role === 'assistant' &&
                    message.rawResponses &&
                    message.rawResponses.length > 0
                "
                class="mt-2 space-y-2"
            >
                <div class="text-[11px] text-muted-foreground">Raw JSON Responses ({{ message.rawResponses.length }}):</div>
                <div
                    v-for="(response, index) in message.rawResponses"
                    :key="`raw-${message.id}-${index}`"
                    class="overflow-x-auto rounded bg-muted/50 p-2 font-mono text-[11px]"
                >
                    <pre>{{ JSON.stringify(response, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>
