<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <Toaster position="top-right" :duration="3000" />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <Toaster position="top-right" :duration="3000" />
    </SidebarProvider>
</template>
