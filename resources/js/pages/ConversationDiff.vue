<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { FileIcon, ChevronLeft, CopyIcon, CheckIcon } from 'lucide-vue-next';

interface Props {
    conversationId: number;
    diffContent: string;
    conversationTitle: string;
    hasContent: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Claude', href: '/claude' },
    { title: 'Conversation', href: `/claude/conversation/${props.conversationId}` },
    { title: 'Diff', href: `/claude/conversation/${props.conversationId}/diff` },
];

const copied = ref(false);

const formattedDiff = computed(() => {
    if (!props.diffContent) return [];
    
    const lines = props.diffContent.split('\n');
    return lines.map((line, index) => ({
        number: index + 1,
        content: line,
        type: line.startsWith('+++') || line.startsWith('---') ? 'header' :
              line.startsWith('+') ? 'addition' : 
              line.startsWith('-') ? 'deletion' : 
              line.startsWith('@@') ? 'chunk' :
              line.startsWith('diff --git') ? 'file' :
              line.startsWith('index ') ? 'meta' :
              'normal'
    }));
});

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.diffContent);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const goBack = () => {
    router.visit(`/claude/conversation/${props.conversationId}`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6 max-w-7xl">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="goBack"
                        class="flex items-center gap-2"
                    >
                        <ChevronLeft class="h-4 w-4" />
                        Back to Conversation
                    </Button>
                    <h1 class="text-2xl font-bold">Git Diff</h1>
                </div>
                <Button
                    v-if="hasContent"
                    variant="outline"
                    size="sm"
                    @click="copyToClipboard"
                    class="flex items-center gap-2"
                >
                    <CopyIcon v-if="!copied" class="h-4 w-4" />
                    <CheckIcon v-else class="h-4 w-4 text-green-600" />
                    {{ copied ? 'Copied!' : 'Copy Diff' }}
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileIcon class="h-5 w-5" />
                        <span class="text-sm text-muted-foreground">
                            {{ conversationTitle }}
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="!hasContent" class="text-center py-12 text-muted-foreground">
                        <p class="text-lg">No diff available</p>
                        <p class="text-sm mt-2">
                            The diff will appear here after Claude makes changes to your project.
                        </p>
                    </div>
                    <div v-else class="relative">
                        <div class="overflow-x-auto bg-slate-900 dark:bg-slate-950 rounded-lg border border-slate-800">
                            <div class="font-mono text-sm">
                                <div 
                                    v-for="line in formattedDiff" 
                                    :key="line.number" 
                                    :class="{
                                        'bg-green-950/30 border-l-4 border-green-500': line.type === 'addition',
                                        'bg-red-950/30 border-l-4 border-red-500': line.type === 'deletion',
                                        'bg-blue-950/50 px-4 py-2 font-semibold': line.type === 'chunk',
                                        'bg-purple-950/50 px-4 py-2 border-b border-slate-800': line.type === 'file',
                                        'bg-yellow-950/30 px-4 py-1': line.type === 'header',
                                        'bg-slate-800/30 px-4 py-0.5 text-xs': line.type === 'meta',
                                        'hover:bg-slate-800/30': line.type === 'normal'
                                    }"
                                    class="flex transition-colors duration-150"
                                >
                                    <span 
                                        class="inline-block w-14 text-right pr-4 select-none text-slate-500 flex-shrink-0 py-1"
                                        :class="{
                                            'bg-slate-900/50': line.type === 'addition' || line.type === 'deletion'
                                        }"
                                    >{{ line.number }}</span>
                                    <pre class="flex-1 overflow-x-auto py-1 pr-4"><code
                                        :class="{
                                            'text-green-400': line.type === 'addition',
                                            'text-red-400': line.type === 'deletion',
                                            'text-blue-400': line.type === 'chunk',
                                            'text-purple-400': line.type === 'file',
                                            'text-yellow-500': line.type === 'header',
                                            'text-slate-500': line.type === 'meta',
                                            'text-slate-300': line.type === 'normal'
                                        }"
                                    >{{ line.content }}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
pre {
    white-space: pre;
    word-wrap: normal;
    overflow-x: auto;
    margin: 0;
}

code {
    display: inline;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
}

.flex-1 pre {
    background: transparent;
    padding: 0;
}
</style>