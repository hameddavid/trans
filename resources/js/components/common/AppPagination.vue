<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-3 py-2 sm:px-4">
    <div class="flex items-center gap-2">
      <label class="text-xs text-gray-700">Show</label>
      <select
        :value="perPage"
        @change="$emit('per-page-change', Number($event.target.value))"
        class="rounded border-gray-300 text-xs py-1 px-2 shadow-sm focus:border-run-blue focus:ring-run-blue"
      >
        <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
      </select>
      <span class="text-xs text-gray-700">entries</span>
    </div>

    <div class="text-xs text-gray-700">
      Showing <span class="font-medium">{{ rangeStart }}</span> to
      <span class="font-medium">{{ rangeEnd }}</span> of
      <span class="font-medium">{{ totalItems }}</span> results
    </div>

    <nav class="isolate inline-flex -space-x-px rounded shadow-sm" aria-label="Pagination">
      <button
        :disabled="currentPage <= 1"
        class="relative inline-flex items-center rounded-l px-1.5 py-1 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="$emit('page-change', currentPage - 1)"
      >
        <ChevronLeftIcon class="h-4 w-4" />
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        :class="[
          'relative inline-flex items-center px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ring-gray-300',
          page === currentPage
            ? 'z-10 bg-run-blue text-white'
            : 'text-gray-900 hover:bg-gray-50',
        ]"
        @click="$emit('page-change', page)"
      >
        {{ page }}
      </button>

      <button
        :disabled="currentPage >= totalPages"
        class="relative inline-flex items-center rounded-r px-1.5 py-1 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="$emit('page-change', currentPage + 1)"
      >
        <ChevronRightIcon class="h-4 w-4" />
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages: { type: Number, required: true },
  totalItems: { type: Number, required: true },
  perPage: { type: Number, default: 15 },
});

defineEmits(['page-change', 'per-page-change']);

const perPageOptions = [10, 15, 25, 50, 100];

const rangeStart = computed(() => {
  if (props.totalItems === 0) return 0;
  return (props.currentPage - 1) * props.perPage + 1;
});

const rangeEnd = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = props.totalPages;
  const current = props.currentPage;
  const delta = 2;

  let start = Math.max(1, current - delta);
  let end = Math.min(total, current + delta);

  if (current - delta < 1) end = Math.min(total, end + (delta - current + 1));
  if (current + delta > total) start = Math.max(1, start - (current + delta - total));

  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});
</script>
