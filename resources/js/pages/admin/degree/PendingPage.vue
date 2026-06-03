<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Pending Degree Verifications</h1>

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
              <td class="px-4 py-2 whitespace-nowrap text-xs space-x-2 sticky-action-col">
                <button
                  @click="openTreatModal(item)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-blue text-white text-xs font-medium rounded-md hover:bg-blue-600"
                >
                  Treat
                </button>
                <button
                  @click="handleRecommend(item)"
                  class="inline-flex items-center px-3 py-1.5 bg-run-gold text-run-blue text-xs font-medium rounded-md hover:bg-run-gold/90"
                >
                  Recommend
                </button>
              </td>
            </tr>
            <tr v-if="filteredItems.length === 0">
              <td colspan="6" class="px-4 py-5 text-center text-xs text-gray-500">
                <DocumentIcon class="mx-auto h-12 w-12 text-gray-400 mb-2" />
                No pending degree verifications found
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
        @page-change="(p) => degreeVerificationStore.fetchPending(p)"
        @per-page-change="(pp) => degreeVerificationStore.fetchPending(1, pp)"
      />
    </div>

    <div v-if="showTreatModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showTreatModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Treat Degree Verification</h3>
            <button @click="showTreatModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>

          <div v-if="selectedItem" class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div>
                <span class="text-sm font-medium text-gray-500">Organization:</span>
                <span class="ml-2 text-sm text-gray-900">{{ selectedItem.organization }}</span>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-500">Graduate:</span>
                <span class="ml-2 text-sm text-gray-900">{{ selectedItem.graduate_name }}</span>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-500">Matric Number:</span>
                <span class="ml-2 text-sm text-gray-900">{{ selectedItem.matric_number }}</span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Verification Result</label>
              <textarea
                v-model="treatForm.result"
                rows="4"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-run-blue focus:ring-run-blue sm:text-sm"
                placeholder="Enter verification result..."
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                @click="showTreatModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="submitTreat"
                :disabled="!treatForm.result.trim()"
                class="px-4 py-2 text-sm font-medium text-white bg-run-blue rounded-md hover:bg-blue-600 disabled:opacity-50"
              >
                Submit
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useDegreeVerificationStore } from '@/stores/degreeVerification';
import AppPagination from '@/components/common/AppPagination.vue';
import { DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const degreeVerificationStore = useDegreeVerificationStore();
const pag = computed(() => degreeVerificationStore.pagination.pending);

const searchQuery = ref('');
const showTreatModal = ref(false);
const selectedItem = ref(null);
const treatForm = reactive({ result: '' });

const rowNumber = (index) => (pag.value.currentPage - 1) * pag.value.perPage + index + 1;

const filteredItems = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return degreeVerificationStore.pending;
  return degreeVerificationStore.pending.filter(
    (item) =>
      item.organization?.toLowerCase().includes(q) ||
      item.graduate_name?.toLowerCase().includes(q) ||
      item.matric_number?.toLowerCase().includes(q)
  );
});

function openTreatModal(item) {
  selectedItem.value = item;
  treatForm.result = '';
  showTreatModal.value = true;
}

async function submitTreat() {
  if (!treatForm.result.trim()) return;
  await degreeVerificationStore.treatDegree({
    id: selectedItem.value.id,
    result: treatForm.result,
  });
  showTreatModal.value = false;
  degreeVerificationStore.fetchPending(pag.value.currentPage);
}

async function handleRecommend(item) {
  if (!window.confirm(`Recommend degree verification for ${item.graduate_name}?`)) return;
  await degreeVerificationStore.recommendDegree({ id: item.id });
  degreeVerificationStore.fetchPending(pag.value.currentPage);
}

onMounted(() => {
  degreeVerificationStore.fetchPending();
});
</script>
