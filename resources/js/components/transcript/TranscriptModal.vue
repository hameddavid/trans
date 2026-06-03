<template>
  <AppModal :show="show" :title="title" size="xl" @close="$emit('close')">
    <TranscriptViewer :html="html" />

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton variant="outline" size="sm" @click="handlePrint">
          <PrinterIcon class="h-4 w-4 mr-1.5" />
          Print
        </AppButton>
        <AppButton variant="secondary" size="sm" @click="$emit('close')">
          Close
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { PrinterIcon } from '@heroicons/vue/24/outline';
import AppModal from '@/components/common/AppModal.vue';
import AppButton from '@/components/common/AppButton.vue';
import TranscriptViewer from './TranscriptViewer.vue';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  html: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: 'Transcript Preview',
  },
});

defineEmits(['close']);

function handlePrint() {
  const content = document.querySelector('.transcript-content');
  if (!content) return;
  const printWindow = window.open('', '_blank');
  if (!printWindow) return;
  printWindow.document.write(`
    <html>
      <head><title>Transcript</title></head>
      <body>${content.innerHTML}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}
</script>
