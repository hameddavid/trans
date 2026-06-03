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
              <td colspan="6" class="px-4 py-5 text-center text-xs text-gray-500">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAdminApplicationsStore } from '@/stores/adminApplications';
import * as adminApi from '@/api/adminApi';
import AppPagination from '@/components/common/AppPagination.vue';
import { DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const adminApplicationsStore = useAdminApplicationsStore();
const pag = computed(() => adminApplicationsStore.pagination.approvedOfficial);

const searchQuery = ref('');
const showTranscriptModal = ref(false);
const transcriptHtml = ref('');
const transcriptLoading = ref(false);

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

onMounted(() => {
  adminApplicationsStore.fetchApprovedOfficial();
});
</script>
