<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Approved Degree Verifications</h1>

    <div>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by organization, graduate name, or matric number..."
        class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-run-blue focus:ring-run-blue sm:text-sm"
      />
    </div>

    <div v-if="degreeVerificationStore.loading" class="flex justify-center py-12">
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
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Requesting Organization</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Graduate Name</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in filteredItems" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ rowNumber(index) }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ item.organization }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ item.graduate_name }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ item.matric_number }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ item.created_at }}</td>
              <td class="px-4 py-2 whitespace-nowrap text-xs">
                <button
                  @click="viewDocument(item)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-blue text-white text-xs font-medium rounded-md hover:bg-blue-600"
                >
                  View Document
                </button>
              </td>
            </tr>
            <tr v-if="filteredItems.length === 0">
              <td colspan="6" class="px-4 py-5 text-center text-xs text-gray-500">
                <DocumentIcon class="mx-auto h-12 w-12 text-gray-400 mb-2" />
                No approved degree verifications found
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
        @page-change="(p) => degreeVerificationStore.fetchApproved(p)"
        @per-page-change="(pp) => degreeVerificationStore.fetchApproved(1, pp)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useDegreeVerificationStore } from '@/stores/degreeVerification';
import * as adminApi from '@/api/adminApi';
import AppPagination from '@/components/common/AppPagination.vue';
import { DocumentIcon } from '@heroicons/vue/24/outline';

const degreeVerificationStore = useDegreeVerificationStore();
const pag = computed(() => degreeVerificationStore.pagination.approved);

const searchQuery = ref('');

const rowNumber = (index) => (pag.value.currentPage - 1) * pag.value.perPage + index + 1;

const filteredItems = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return degreeVerificationStore.approved;
  return degreeVerificationStore.approved.filter(
    (item) =>
      item.organization?.toLowerCase().includes(q) ||
      item.graduate_name?.toLowerCase().includes(q) ||
      item.matric_number?.toLowerCase().includes(q)
  );
});

async function viewDocument(item) {
  try {
    const response = await adminApi.viewDegreeDocument(item.document_path ?? item.id);
    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
    window.open(url, '_blank');
  } catch {
    alert('Failed to load document.');
  }
}

onMounted(() => {
  degreeVerificationStore.fetchApproved();
});
</script>
