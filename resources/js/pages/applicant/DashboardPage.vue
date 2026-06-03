<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">
      Welcome, {{ authStore.user?.surname }} {{ authStore.user?.firstname }}
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
      <div class="bg-green-50 rounded-lg border-l-4 border-run-gold p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-green-700">Successful Applications</p>
            <p class="text-2xl font-bold text-green-900 mt-0.5">{{ stats.successful }}</p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-run-gold" />
        </div>
      </div>

      <div class="bg-yellow-50 rounded-lg border-l-4 border-yellow-400 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-yellow-700">Pending Applications</p>
            <p class="text-2xl font-bold text-yellow-900 mt-0.5">{{ stats.pending }}</p>
          </div>
          <ClockIcon class="w-8 h-8 text-yellow-400" />
        </div>
      </div>

      <div class="bg-red-50 rounded-lg border-l-4 border-red-400 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-red-700">Failed Applications</p>
            <p class="text-2xl font-bold text-red-900 mt-0.5">{{ stats.failed }}</p>
          </div>
          <XCircleIcon class="w-8 h-8 text-red-400" />
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">Recent Transactions</h2>
        <router-link
          :to="{ name: 'applicant-payments' }"
          class="text-xs text-run-blue hover:text-run-blue/80 font-medium"
        >
          View all payments &rarr;
        </router-link>
      </div>

      <div v-if="loadingPayments" class="p-5 text-center text-xs text-gray-500">
        Loading transactions...
      </div>

      <div v-else-if="recentPayments.length === 0" class="p-5 text-center text-xs text-gray-500">
        No transactions found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Date</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">RRR</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Amount</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Type</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="payment in recentPayments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ formatDate(payment.created_at) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700 font-mono">{{ payment.rrr || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-900 font-medium">&#x20A6;{{ formatAmount(payment.amount) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ payment.type }}</td>
              <td class="px-4 py-2 whitespace-nowrap">
                <span :class="statusBadgeClass(payment.status)">{{ payment.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- My Complaints -->
    <div v-if="complaints.length > 0" class="bg-white rounded-lg shadow mt-4">
      <div class="px-4 py-3 border-b border-gray-200">
        <h2 class="text-sm font-semibold text-gray-900">My Complaints</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Date</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Subject</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Status</th>
              <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider text-[11px]">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="c in complaints" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ formatDate(c.created_at) }}</td>
              <td class="px-4 py-2 text-xs text-gray-700">{{ c.subject }}</td>
              <td class="px-4 py-2 whitespace-nowrap">
                <span :class="c.status === 'RESOLVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ c.status }}
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                <button @click="viewComplaint(c)" class="text-run-blue hover:underline text-sm font-medium">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Complaint Detail Modal -->
    <div v-if="selectedComplaint" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="selectedComplaint = null" />
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-4 z-10">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Complaint Details</h3>
        <div class="space-y-3 text-sm">
          <div>
            <p class="text-gray-500">Subject:</p>
            <p class="font-medium text-gray-900">{{ selectedComplaint.subject }}</p>
          </div>
          <div>
            <p class="text-gray-500 mb-1">Your Message:</p>
            <p class="text-gray-800 bg-gray-50 rounded-lg p-3 whitespace-pre-wrap">{{ selectedComplaint.message }}</p>
          </div>
          <div>
            <p class="text-gray-500">Status:</p>
            <span :class="selectedComplaint.status === 'RESOLVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
              {{ selectedComplaint.status }}
            </span>
          </div>
          <div v-if="selectedComplaint.admin_response" class="border-t pt-3">
            <p class="text-gray-500 mb-1">Admin Response:</p>
            <p class="text-gray-800 bg-green-50 rounded-lg p-3 whitespace-pre-wrap">{{ selectedComplaint.admin_response }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ formatDate(selectedComplaint.responded_at) }}</p>
          </div>
          <div v-else class="border-t pt-3">
            <p class="text-gray-500 italic">Awaiting admin response...</p>
          </div>
        </div>
        <div class="flex justify-end mt-4">
          <button @click="selectedComplaint = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { CheckCircleIcon, ClockIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/stores/auth';
import { useApplicationStore } from '@/stores/application';
import { usePaymentStore } from '@/stores/payment';
import * as applicantApi from '@/api/applicantApi';

const authStore = useAuthStore();
const applicationStore = useApplicationStore();
const paymentStore = usePaymentStore();

const stats = computed(() => applicationStore.stats || { successful: 0, pending: 0, failed: 0 });
const loadingPayments = computed(() => paymentStore.loading);
const recentPayments = computed(() => (paymentStore.payments || []).slice(0, 5));
const complaints = ref([]);
const selectedComplaint = ref(null);

function viewComplaint(c) {
  selectedComplaint.value = c;
}

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
    SUCCESSFUL: `${base} bg-green-100 text-green-800`,
    PENDING: `${base} bg-yellow-100 text-yellow-800`,
    FAILED: `${base} bg-red-100 text-red-800`,
  };
  return map[(status || '').toUpperCase()] || `${base} bg-gray-100 text-gray-800`;
}

async function fetchComplaints() {
  try {
    const { data } = await applicantApi.getMyComplaints();
    complaints.value = data.data ?? data;
  } catch {
    // silent
  }
}

onMounted(() => {
  applicationStore.fetchStats();
  paymentStore.fetchMyPayments();
  fetchComplaints();
});
</script>
