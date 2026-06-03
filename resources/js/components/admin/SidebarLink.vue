<template>
  <router-link
    :to="to"
    class="flex items-center space-x-2 px-2.5 py-1.5 text-xs rounded-md transition"
    :class="isActive
      ? 'bg-run-blue/20 text-run-gold border-l-3 border-run-gold'
      : 'text-gray-400 hover:text-white hover:bg-gray-700'"
    v-bind="$attrs"
  >
    <component :is="icon" class="w-4 h-4 flex-shrink-0" />
    <span>{{ label }}</span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  to: { type: [String, Object], required: true },
  label: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
});

const route = useRoute();

const isActive = computed(() => {
  if (typeof props.to === 'object' && props.to.name) {
    return route.name === props.to.name;
  }
  return route.path === props.to;
});
</script>
