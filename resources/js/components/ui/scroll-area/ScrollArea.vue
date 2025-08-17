<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ScrollAreaCorner, ScrollAreaRoot, ScrollAreaScrollbar, ScrollAreaThumb, ScrollAreaViewport } from 'reka-ui';

interface ScrollAreaProps {
    class?: string;
    orientation?: 'vertical' | 'horizontal' | 'both';
}

const props = withDefaults(defineProps<ScrollAreaProps>(), {
    orientation: 'vertical',
});
</script>

<template>
    <ScrollAreaRoot :class="cn('relative overflow-hidden', props.class)">
        <ScrollAreaViewport class="h-full w-full rounded-[inherit]">
            <slot />
        </ScrollAreaViewport>
        <ScrollAreaScrollbar
            v-if="orientation === 'vertical' || orientation === 'both'"
            class="flex touch-none select-none transition-colors h-full w-2.5 border-l border-l-transparent p-[1px]"
            orientation="vertical"
        >
            <ScrollAreaThumb class="relative flex-1 rounded-full bg-border" />
        </ScrollAreaScrollbar>
        <ScrollAreaScrollbar
            v-if="orientation === 'horizontal' || orientation === 'both'"
            class="flex touch-none select-none transition-colors h-2.5 flex-col border-t border-t-transparent p-[1px]"
            orientation="horizontal"
        >
            <ScrollAreaThumb class="relative flex-1 rounded-full bg-border" />
        </ScrollAreaScrollbar>
        <ScrollAreaCorner />
    </ScrollAreaRoot>
</template>