<template>
  <div>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <AppSpinner size="lg" />
    </div>
    <template v-else>
      <div class="flex justify-end mb-4">
        <AppButton variant="outline" size="sm" @click="handlePrint">
          <PrinterIcon class="h-4 w-4 mr-1.5" />
          Print
        </AppButton>
      </div>
      <div ref="contentRef" class="transcript-content prose max-w-none" v-html="html" />
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { PrinterIcon } from '@heroicons/vue/24/outline';
import AppSpinner from '@/components/common/AppSpinner.vue';
import AppButton from '@/components/common/AppButton.vue';

defineProps({
  html: {
    type: String,
    default: '',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const contentRef = ref(null);

function handlePrint() {
  const printWindow = window.open('', '_blank');
  if (!printWindow) return;
  printWindow.document.write(`
    <html>
      <head><title>Transcript</title></head>
      <body>${contentRef.value?.innerHTML || ''}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}
</script>

<style scoped>
.transcript-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}
.transcript-content :deep(table th),
.transcript-content :deep(table td) {
  border: 1px solid #d1d5db;
  padding: 0.5rem 0.75rem;
  text-align: left;
  font-size: 0.875rem;
}
.transcript-content :deep(table th) {
  background-color: #f3f4f6;
  font-weight: 600;
}
.transcript-content :deep(h1),
.transcript-content :deep(h2),
.transcript-content :deep(h3) {
  color: #1a1d21;
  margin-bottom: 0.5rem;
}
.transcript-content :deep(p) {
  margin-bottom: 0.5rem;
  line-height: 1.6;
}
</style>
