<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useAttrs } from 'vue';

interface TextareaProps {
    class?: string;
    defaultValue?: string | number;
    modelValue?: string | number;
}

const props = defineProps<TextareaProps>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const attrs = useAttrs();

const handleInput = (event: Event) => {
    emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
};
</script>

<template>
    <textarea
        :class="
            cn(
                'flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
                props.class
            )
        "
        :value="modelValue"
        v-bind="attrs"
        @input="handleInput"
    />
</template>