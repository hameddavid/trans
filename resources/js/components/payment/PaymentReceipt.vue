<template>
  <div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" ref="receiptRef">
      <div class="text-center py-6 border-b border-gray-200">
        <img src="/images/logo.png" alt="University Logo" class="h-16 mx-auto mb-2" />
        <h2 class="text-lg font-bold text-gray-900">Redeemer's University</h2>
        <p class="text-sm text-gray-500 mt-1">Payment Receipt</p>
      </div>

      <div class="p-6">
        <table class="w-full text-sm">
          <tbody class="divide-y divide-gray-200">
            <tr>
              <td class="py-3 font-medium text-gray-500">Amount</td>
              <td class="py-3 text-right text-gray-900 font-semibold">
                &#8358;{{ Number(payment.amount).toLocaleString() }}
              </td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">RRR</td>
              <td class="py-3 text-right text-gray-900">{{ payment.rrr }}</td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">Transaction ID</td>
              <td class="py-3 text-right text-gray-900">{{ payment.transactionId }}</td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">Date</td>
              <td class="py-3 text-right text-gray-900">{{ payment.date }}</td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">Name</td>
              <td class="py-3 text-right text-gray-900">{{ payment.name }}</td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">Type</td>
              <td class="py-3 text-right text-gray-900">{{ payment.type }}</td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-gray-500">Status</td>
              <td class="py-3 text-right">
                <AppBadge :variant="statusVariant">{{ payment.status }}</AppBadge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center">
        <p class="text-xs text-gray-400 italic">This is a computer generated receipt</p>
      </div>
    </div>

    <div class="flex justify-center mt-4">
      <AppButton variant="outline" size="sm" @click="handlePrint">
        <PrinterIcon class="h-4 w-4 mr-1.5" />
        Print Receipt
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { PrinterIcon } from '@heroicons/vue/24/outline';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';

const props = defineProps({
  payment: {
    type: Object,
    required: true,
  },
});

const receiptRef = ref(null);

const statusVariant = computed(() => {
  const s = props.payment.status?.toLowerCase();
  if (s === 'successful' || s === 'success' || s === 'paid') return 'success';
  if (s === 'pending') return 'warning';
  if (s === 'failed') return 'danger';
  return 'neutral';
});

function handlePrint() {
  const printWindow = window.open('', '_blank');
  if (!printWindow) return;
  printWindow.document.write(`
    <html>
      <head>
        <title>Payment Receipt</title>
        <style>
          body { font-family: sans-serif; padding: 2rem; }
          table { width: 100%; border-collapse: collapse; }
          td { padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
          .center { text-align: center; }
          .right { text-align: right; }
          .label { color: #6b7280; }
        </style>
      </head>
      <body>${receiptRef.value?.innerHTML || ''}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}
</script>
