<script setup lang="ts">
import { computed } from 'vue'
import { Icons, type IconName } from '@/utils/icons'

interface Props {
  name: IconName
  size?: number
}

const props = withDefaults(defineProps<Props>(), {
  size: 24
})

const svgContent = computed(() => {
  return Icons[props.name] || ''
})
</script>

<template>
  <svg 
    class="harmonyos-icon" 
    :width="size" 
    :height="size" 
    viewBox="0 0 24 24" 
    fill="currentColor" 
    xmlns="http://www.w3.org/2000/svg"
  >
    <g v-html="svgContent" />
  </svg>
</template>

<style scoped>
.harmonyos-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
  flex-shrink: 0;
}
</style>
