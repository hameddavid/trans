<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div v-if="title" class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
    </div>
    <div :class="padding ? 'p-6' : ''">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  padding: {
    type: Boolean,
    default: true,
  },
});
</script>
