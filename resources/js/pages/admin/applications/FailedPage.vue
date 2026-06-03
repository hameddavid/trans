<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Failed Official Applications</h1>

    <div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by name or matric number..."
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
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Applicant Name</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Destination</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(app, index) in filteredApplications" :key="app.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ rowNumber(index) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.applicant_name }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.matric_number }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.destination }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.transcript_type }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ app.created_at }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs">
                <button
                  @click="viewDetails(app)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-blue text-white text-xs font-medium rounded-md hover:bg-blue-600"
                >
                  View Details
                </button>
              </td>
            </tr>
            <tr v-if="filteredApplications.length === 0">
              <td colspan="7" class="px-4 py-5 text-center text-xs text-gray-500">
                <ExclamationCircleIcon class="mx-auto h-12 w-12 text-gray-400 mb-2" />
                No failed official applications found
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
        @page-change="(p) => adminApplicationsStore.fetchFailedOfficial(p)"
        @per-page-change="(pp) => adminApplicationsStore.fetchFailedOfficial(1, pp)"
      />
    </div>

    <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Application Details</h3>
            <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>
          <div v-if="selectedApp" class="space-y-3">
            <div>
              <span class="text-sm font-medium text-gray-500">Applicant:</span>
              <span class="ml-2 text-sm text-gray-900">{{ selectedApp.applicant_name }}</span>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-500">Matric Number:</span>
              <span class="ml-2 text-sm text-gray-900">{{ selectedApp.matric_number }}</span>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-500">Destination:</span>
              <span class="ml-2 text-sm text-gray-900">{{ selectedApp.destination }}</span>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-500">Type:</span>
              <span class="ml-2 text-sm text-gray-900">{{ selectedApp.transcript_type }}</span>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-500">Date Applied:</span>
              <span class="ml-2 text-sm text-gray-900">{{ selectedApp.created_at }}</span>
            </div>
            <div v-if="selectedApp.failure_reason">
              <span class="text-sm font-medium text-gray-500">Failure Reason:</span>
              <p class="mt-1 text-sm text-red-600">{{ selectedApp.failure_reason }}</p>
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
import AppPagination from '@/components/common/AppPagination.vue';
import { ExclamationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const adminApplicationsStore = useAdminApplicationsStore();
const pag = computed(() => adminApplicationsStore.pagination.failedOfficial);

const searchQuery = ref('');
const showDetailsModal = ref(false);
const selectedApp = ref(null);

const rowNumber = (index) => (pag.value.currentPage - 1) * pag.value.perPage + index + 1;

const filteredApplications = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return adminApplicationsStore.failedOfficial;
  return adminApplicationsStore.failedOfficial.filter(
    (app) =>
      app.applicant_name?.toLowerCase().includes(q) ||
      app.matric_number?.toLowerCase().includes(q)
  );
});

function viewDetails(app) {
  selectedApp.value = app;
  showDetailsModal.value = true;
}

onMounted(() => {
  adminApplicationsStore.fetchFailedOfficial();
});
</script>
