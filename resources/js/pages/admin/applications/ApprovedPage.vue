<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Approved Official Applications</h1>

    <div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by name, matric number, or destination..."
        class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-run-blue focus:ring-run-blue sm:text-sm"
      />
    </div>

    <div v-if="adminApplicationsStore.loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-run-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
      </svg>
    </div>

    <div v-else>
      <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Destination</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Delivery</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Courier</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date Approved</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(app, index) in filteredApplications" :key="app.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ rowNumber(index) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.applicant_name }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.matric_number }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.destination }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900 capitalize">{{ app.delivery_mode || '-' }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs">
                <span v-if="!app.courier_status" class="text-gray-400">-</span>
                <span v-else-if="app.courier_status === 'pending'" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800">Awaiting</span>
                <button v-else-if="app.courier_status === 'submitted'" @click="viewCourier(app)" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 cursor-pointer">Review</button>
                <span v-else-if="app.courier_status === 'verified'" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">Verified</span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.approved_at }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs space-x-2 sticky-action-col">
                <button
                  @click="viewTranscript(app)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-blue text-white text-xs font-medium rounded-md hover:bg-blue-600"
                >
                  View Transcript
                </button>
                <button
                  @click="downloadPdf(app)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-gold text-run-blue text-xs font-medium rounded-md hover:bg-run-gold/90"
                >
                  Download PDF
                </button>
                <button
                  @click="handleRegenerate(app)"
                  class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600"
                >
                  Regenerate
                </button>
              </td>
            </tr>
            <tr v-if="filteredApplications.length === 0">
              <td colspan="8" class="px-4 py-5 text-center text-xs text-gray-500">
                <DocumentIcon class="mx-auto h-12 w-12 text-gray-400 mb-2" />
                No approved official applications found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <AppPagination
        v-if="pag.total > 0"
        :current-page="pag.currentPage"
        :total-pages="pag.lastPage"
        :total-items="pag.total"
        :per-page="pag.perPage"
        @page-change="(p) => adminApplicationsStore.fetchApprovedOfficial(p)"
        @per-page-change="(pp) => adminApplicationsStore.fetchApprovedOfficial(1, pp)"
      />
    </div>

    <div v-if="showTranscriptModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showTranscriptModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-y-auto p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transcript Preview</h3>
            <button @click="showTranscriptModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>
          <div v-if="transcriptLoading" class="flex justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-run-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
          </div>
          <div v-else v-html="transcriptHtml" class="prose max-w-none"></div>
        </div>
      </div>
    </div>
    <!-- Courier Review Modal -->
    <div v-if="showCourierModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showCourierModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[80vh] overflow-y-auto p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Courier Details Review</h3>
            <button @click="showCourierModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>

          <div v-if="courierApp" class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Applicant</span><span class="font-medium text-gray-900">{{ courierApp.applicant_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Application ID</span><span class="font-mono text-gray-900">{{ courierApp.id }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Destination</span><span class="font-medium text-gray-900">{{ courierApp.destination }}</span></div>

            <div class="border-t pt-3 mt-3">
              <h4 class="font-semibold text-gray-900 mb-2">Submitted Details</h4>
              <div class="space-y-1.5">
                <div class="flex justify-between"><span class="text-gray-500">Company</span><span class="font-medium text-gray-900">{{ courierApp.courier_company }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="font-medium text-gray-900">{{ courierApp.courier_contact }}</span></div>
                <div v-if="courierApp.courier_tracking" class="flex justify-between"><span class="text-gray-500">Tracking #</span><span class="font-medium text-gray-900">{{ courierApp.courier_tracking }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Submitted</span><span class="font-medium text-gray-900">{{ courierApp.courier_submitted_at }}</span></div>
              </div>

              <div class="mt-3">
                <button @click="viewReceipt(courierApp)" :disabled="receiptLoading"
                  class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200">
                  {{ receiptLoading ? 'Loading...' : 'View Payment Receipt' }}
                </button>
              </div>
            </div>

            <div class="border-t pt-3 mt-3">
              <label class="block text-xs font-medium text-gray-600 mb-1">Notes (optional)</label>
              <textarea v-model="courierNotes" rows="2" placeholder="Add a note for the applicant..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"></textarea>
            </div>

            <div class="flex gap-2 pt-2">
              <button @click="handleCourierAction('verify')" :disabled="courierActioning"
                class="flex-1 bg-green-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50">
                {{ courierActioning ? 'Processing...' : 'Verify & Approve' }}
              </button>
              <button @click="handleCourierAction('reject')" :disabled="courierActioning"
                class="flex-1 bg-red-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50">
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAdminApplicationsStore } from '@/stores/adminApplications';
import { useToast } from 'vue-toastification';
import * as adminApi from '@/api/adminApi';
import AppPagination from '@/components/common/AppPagination.vue';
import { DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const adminApplicationsStore = useAdminApplicationsStore();
const pag = computed(() => adminApplicationsStore.pagination.approvedOfficial);

const searchQuery = ref('');
const showTranscriptModal = ref(false);
const transcriptHtml = ref('');
const transcriptLoading = ref(false);

const showCourierModal = ref(false);
const courierApp = ref(null);
const courierNotes = ref('');
const courierActioning = ref(false);
const receiptLoading = ref(false);

const rowNumber = (index) => (pag.value.currentPage - 1) * pag.value.perPage + index + 1;

const filteredApplications = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return adminApplicationsStore.approvedOfficial;
  return adminApplicationsStore.approvedOfficial.filter(
    (app) =>
      app.applicant_name?.toLowerCase().includes(q) ||
      app.matric_number?.toLowerCase().includes(q) ||
      app.destination?.toLowerCase().includes(q)
  );
});

async function viewTranscript(app) {
  showTranscriptModal.value = true;
  transcriptLoading.value = true;
  try {
    const { data } = await adminApi.getTranscriptHtml('official', app.id);
    transcriptHtml.value = data.html ?? data;
  } catch {
    transcriptHtml.value = '<p class="text-red-500">Failed to load transcript.</p>';
  } finally {
    transcriptLoading.value = false;
  }
}

async function downloadPdf(app) {
  try {
    const response = await adminApplicationsStore.downloadApproved({ application_id: app.id });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transcript-${app.matric_number}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch {
    alert('Failed to download PDF.');
  }
}

async function handleRegenerate(app) {
  if (!window.confirm(`Regenerate transcript for ${app.applicant_name}?`)) return;
  await adminApplicationsStore.regenerateTranscript({ application_id: app.id });
  adminApplicationsStore.fetchApprovedOfficial(pag.value.currentPage);
}

function viewCourier(app) {
  courierApp.value = app;
  courierNotes.value = '';
  showCourierModal.value = true;
}

async function viewReceipt(app) {
  receiptLoading.value = true;
  try {
    const response = await adminApi.viewCourierReceipt(app.id);
    const blob = new Blob([response.data], { type: response.headers['content-type'] });
    const url = window.URL.createObjectURL(blob);
    window.open(url, '_blank');
  } catch {
    toast.error('Failed to load receipt.');
  } finally {
    receiptLoading.value = false;
  }
}

async function handleCourierAction(action) {
  if (action === 'reject' && !courierNotes.value) {
    toast.error('Please provide a reason for rejection.');
    return;
  }
  courierActioning.value = true;
  try {
    const { data } = await adminApi.courierAction({
      application_id: courierApp.value.id,
      action,
      notes: courierNotes.value,
    });
    toast.success(data.message);
    showCourierModal.value = false;
    adminApplicationsStore.fetchApprovedOfficial(pag.value.currentPage);
  } catch (e) {
    toast.error(e.response?.data?.message || 'Action failed.');
  } finally {
    courierActioning.value = false;
  }
}

onMounted(() => {
  adminApplicationsStore.fetchApprovedOfficial();
});
</script>
