<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const isTransitioning = ref(false);

router.on('before', () => {
    isTransitioning.value = true;
});

router.on('navigate', () => {
    isTransitioning.value = false;
});

router.on('finish', () => {
    isTransitioning.value = false;
});
</script>

<template>
    <Transition name="page-transition" mode="out-in">
        <div :key="$page.url" :class="{ 'pointer-events-none': isTransitioning }">
            <slot />
        </div>
    </Transition>
</template>

<style scoped>
.page-transition-enter-active {
    transition: opacity 0.15s ease-out;
}

.page-transition-leave-active {
    transition: opacity 0.1s ease-in;
}

.page-transition-enter-from {
    opacity: 0;
}

.page-transition-leave-to {
    opacity: 0;
}
</style>
