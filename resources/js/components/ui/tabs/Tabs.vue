<script setup lang="ts">
import { TabsRoot, type TabsRootProps, useForwardPropsEmits } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsRootProps {
  class?: string
  defaultValue?: string
  modelValue?: string
}

const props = defineProps<Props>()

const forwarded = useForwardPropsEmits(props, ['class'])
</script>

<template>
  <TabsRoot v-bind="forwarded" :class="cn(props.class)">
    <slot />
  </TabsRoot>
</template>