<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">My Payments</h1>

    <div class="bg-white rounded-lg shadow">
      <div v-if="loading" class="p-8 text-center text-gray-500">
        Loading payments...
      </div>

      <div v-else-if="payments.length === 0" class="p-8 text-center">
        <BanknotesIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
        <p class="text-gray-500 mb-4">No payments found.</p>
        <router-link
          :to="{ name: 'applicant-apply' }"
          class="text-run-blue hover:text-run-blue/80 font-medium text-sm"
        >
          Apply for a transcript &rarr;
        </router-link>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">RRR</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ formatDate(payment.created_at) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700 font-mono">{{ payment.rrr || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-900 font-medium">&#x20A6;{{ formatAmount(payment.amount) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ payment.type || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap">
                <span :class="statusBadgeClass(payment.status)">{{ payment.status }}</span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                <button
                  v-if="(payment.status || '').toUpperCase() === 'PENDING' && payment.rrr"
                  @click="requery(payment)"
                  :disabled="requerying === payment.rrr"
                  class="text-run-blue hover:underline text-sm font-medium disabled:opacity-50"
                >
                  {{ requerying === payment.rrr ? 'Checking...' : 'Re-query' }}
                </button>
                <span v-else-if="(payment.status || '').toUpperCase() === 'SUCCESS'" class="text-green-600 text-sm">Paid</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { BanknotesIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import { usePaymentStore } from '@/stores/payment';
import * as applicantApi from '@/api/applicantApi';

const toast = useToast();
const paymentStore = usePaymentStore();
const requerying = ref(null);

const loading = computed(() => paymentStore.loading);
const payments = computed(() => paymentStore.payments || []);

function formatDate(dateStr) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatAmount(amount) {
  if (!amount) return '0.00';
  return Number(amount).toLocaleString('en-NG', { minimumFractionDigits: 2 });
}

function statusBadgeClass(status) {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
  const map = {
    PAID: `${base} bg-green-100 text-green-800`,
    SUCCESS: `${base} bg-green-100 text-green-800`,
    SUCCESSFUL: `${base} bg-green-100 text-green-800`,
    PENDING: `${base} bg-yellow-100 text-yellow-800`,
    FAILED: `${base} bg-red-100 text-red-800`,
  };
  return map[(status || '').toUpperCase()] || `${base} bg-gray-100 text-gray-800`;
}

async function requery(payment) {
  requerying.value = payment.rrr;
  try {
    const { data } = await applicantApi.verifyPayment({ rrr: payment.rrr });
    if (data.status === 'success') {
      payment.status = 'SUCCESS';
      toast.success('Payment verified successfully! You can now submit your application.');
    } else {
      toast.warning('Payment is still pending. Please complete payment on Remita first.');
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to verify payment. Try again later.');
  } finally {
    requerying.value = null;
  }
}

onMounted(() => {
  paymentStore.fetchMyPayments();
});
</script>
