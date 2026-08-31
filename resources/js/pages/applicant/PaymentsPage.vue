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
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ formatDate(payment.created_at) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700 font-mono text-xs">{{ payment.rrr || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-900 font-medium">&#x20A6;{{ formatAmount(payment.amount) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ payment.type || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap">
                <span :class="gatewayBadgeClass(payment.gateway)">{{ payment.gateway || '-' }}</span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                <span :class="statusBadgeClass(payment.status)">{{ payment.status }}</span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap flex items-center gap-2">
                <button
                  v-if="(payment.status || '').toUpperCase() === 'PENDING' && payment.rrr"
                  @click="requery(payment)"
                  :disabled="requerying === payment.rrr"
                  class="text-run-blue hover:underline text-sm font-medium disabled:opacity-50"
                >
                  {{ requerying === payment.rrr ? 'Checking...' : 'Re-query' }}
                </button>
                <button
                  v-if="(payment.status || '').toUpperCase() === 'SUCCESS'"
                  @click="printReceipt(payment)"
                  class="text-run-blue hover:underline text-sm font-medium flex items-center gap-1"
                >
                  <PrinterIcon class="w-4 h-4" />
                  Receipt
                </button>
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
import { BanknotesIcon, PrinterIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import { usePaymentStore } from '@/stores/payment';
import { useAuthStore } from '@/stores/auth';
import * as applicantApi from '@/api/applicantApi';

const toast = useToast();
const paymentStore = usePaymentStore();
const authStore = useAuthStore();
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

function gatewayBadgeClass(gateway) {
  const base = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium';
  const g = (gateway || '').toUpperCase();
  if (g === 'INTERSWITCH') return `${base} bg-blue-50 text-blue-700`;
  if (g === 'REMITA') return `${base} bg-orange-50 text-orange-700`;
  return `${base} bg-gray-50 text-gray-600`;
}

async function requery(payment) {
  requerying.value = payment.rrr;
  try {
    const params = (payment.gateway || '').toUpperCase() === 'INTERSWITCH'
      ? { txn_ref: payment.rrr, amount: payment.amount, gateway: 'INTERSWITCH' }
      : { rrr: payment.rrr };
    const { data } = await applicantApi.verifyPayment(params);
    if (data.status === 'success') {
      payment.status = 'SUCCESS';
      toast.success('Payment verified successfully!');
    } else {
      toast.warning('Payment is still pending. Please complete payment first.');
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to verify payment. Try again later.');
  } finally {
    requerying.value = null;
  }
}

function printReceipt(payment) {
  const user = authStore.user || {};
  const w = window.open('', '_blank', 'width=700,height=600');
  w.document.write(`<!DOCTYPE html><html><head><title>Payment Receipt</title>
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 30px; color: #1a1a1a; }
  .header { text-align: center; border-bottom: 3px solid #1e3a5f; padding-bottom: 16px; margin-bottom: 24px; }
  .header h1 { margin: 0 0 4px; font-size: 20px; color: #1e3a5f; }
  .header p { margin: 0; font-size: 13px; color: #666; }
  .badge { display: inline-block; background: #dcfce7; color: #166534; font-size: 12px; font-weight: 600; padding: 3px 12px; border-radius: 12px; }
  .title { text-align: center; font-size: 15px; font-weight: 600; color: #1e3a5f; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
  td:first-child { color: #6b7280; width: 40%; }
  td:last-child { font-weight: 500; }
  .amount { font-size: 22px; color: #1e3a5f; font-weight: 700; }
  .footer { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 16px; }
  @media print { body { padding: 20px; } }
</style></head><body>
<div class="header">
  <h1>Redeemer's University</h1>
  <p>Transcript Payment Receipt</p>
</div>
<div class="title">Payment Receipt</div>
<table>
  <tr><td>Name</td><td>${user.surname || ''} ${user.firstname || ''}</td></tr>
  <tr><td>Matric Number</td><td>${user.matric_number || payment.matric_number || ''}</td></tr>
  <tr><td>Email</td><td>${user.email || payment.email || ''}</td></tr>
  <tr><td>Transaction Reference</td><td style="font-family:monospace;">${payment.rrr || '-'}</td></tr>
  <tr><td>Payment Gateway</td><td>${payment.gateway || '-'}</td></tr>
  <tr><td>Destination</td><td>${payment.destination || payment.type || '-'}</td></tr>
  <tr><td>Date</td><td>${formatDate(payment.created_at)}</td></tr>
  <tr><td>Status</td><td><span class="badge">${payment.status}</span></td></tr>
  <tr><td>Amount Paid</td><td class="amount">₦${formatAmount(payment.amount)}</td></tr>
</table>
<div class="footer">
  <p>This is a computer-generated receipt and does not require a signature.</p>
  <p>Redeemer's University, Ede, Osun State, Nigeria</p>
</div>
<script>window.onload = function() { window.print(); }<\/script>
</body></html>`);
  w.document.close();
}

onMounted(() => {
  paymentStore.fetchMyPayments();
});
</script>
