<template>
  <div class="mt-3">
    <p class="px-2.5 mb-0.5 text-[10px] font-semibold uppercase tracking-wider text-gray-500">{{ label }}</p>
    <div class="space-y-0.5">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
});
</script>
