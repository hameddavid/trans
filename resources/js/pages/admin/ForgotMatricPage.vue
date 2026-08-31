<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">Forgot Matric Number Requests</h1>

    <div class="flex items-center justify-between mb-6">
      <div class="flex space-x-4">
        <button @click="activeTab = 'pending'" :class="['px-4 py-2 rounded-lg text-sm font-medium', activeTab === 'pending' ? 'bg-run-blue text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">Pending</button>
        <button @click="activeTab = 'treated'" :class="['px-4 py-2 rounded-lg text-sm font-medium', activeTab === 'treated' ? 'bg-run-blue text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">Treated</button>
      </div>
      <div class="relative w-72">
        <input v-model="searchQuery" type="text" placeholder="Search by name, email, phone..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-run-blue focus:border-run-blue" />
        <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-run-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </div>
      <div v-else-if="currentList.length === 0" class="text-center py-12 text-gray-500">No {{ activeTab }} requests found</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Programme</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Year Left</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th v-if="activeTab === 'pending'" class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Actions</th>
            <th v-else class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Treated By</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, i) in currentList" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ rowNumber(i) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ item.surname }} {{ item.firstname }} {{ item.othername }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.email }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.phone }}</td>
            <td class="px-4 py-2 text-xs text-gray-500">{{ item.program }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.date_left }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.created_at }}</td>
            <td v-if="activeTab === 'pending'" class="px-4 py-2 whitespace-nowrap sticky-action-col">
              <button @click="openTreatModal(item)" class="px-3 py-1.5 bg-run-blue text-white text-xs rounded-lg hover:bg-run-blue/90">Treat</button>
            </td>
            <td v-else class="px-4 py-2 text-xs text-gray-500 sticky-action-col">
              <div>{{ item.treated_by }}</div>
              <div class="text-xs text-gray-400">{{ item.treated_at }}</div>
              <div v-if="item.matno_found" class="text-xs text-green-600 font-medium mt-1">{{ item.matno_found }}</div>
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
      @page-change="(p) => fetchData(p)"
      @per-page-change="(pp) => { pag.perPage = pp; fetchData(1, pp); }"
    />

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md z-10">
        <h3 class="text-lg font-semibold mb-4">Treat Request</h3>
        <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm space-y-1">
          <p><span class="font-medium text-gray-700">Name:</span> {{ selectedItem?.surname }} {{ selectedItem?.firstname }} {{ selectedItem?.othername }}</p>
          <p><span class="font-medium text-gray-700">Email:</span> {{ selectedItem?.email }}</p>
          <p><span class="font-medium text-gray-700">Phone:</span> {{ selectedItem?.phone }}</p>
          <p><span class="font-medium text-gray-700">Programme:</span> {{ selectedItem?.program }}</p>
          <p><span class="font-medium text-gray-700">Year Left:</span> {{ selectedItem?.date_left }}</p>
          <p v-if="selectedItem?.matno_found"><span class="font-medium text-green-700">Auto-matched:</span> {{ selectedItem.matno_found }}</p>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Matric Number <span class="text-red-500">*</span></label>
          <input v-model="treatForm.retrieve_matno" type="text" class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" placeholder="Enter matric number to send to applicant" />
          <p class="mt-1 text-xs text-gray-500">This will be emailed to the applicant.</p>
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="showModal = false" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
          <button @click="submitTreat" :disabled="treating" class="px-4 py-2 text-sm bg-run-blue text-white rounded-lg hover:bg-run-blue/90 disabled:opacity-50">
            {{ treating ? 'Submitting...' : 'Submit' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import * as adminApi from '@/api/adminApi'
import AppPagination from '@/components/common/AppPagination.vue'

const activeTab = ref('pending')
const allItems = ref([])
const loading = ref(true)
const showModal = ref(false)
const selectedItem = ref(null)
const treating = ref(false)
const treatForm = ref({ retrieve_matno: '' })
const searchQuery = ref('')
const pag = reactive({ currentPage: 1, lastPage: 1, total: 0, perPage: 15 })

const rowNumber = (index) => (pag.currentPage - 1) * pag.perPage + index + 1

const currentList = computed(() => {
  const tabFiltered = allItems.value.filter(r =>
    activeTab.value === 'pending' ? r.status === 'PENDING' : r.status === 'TREATED'
  )
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return tabFiltered
  return tabFiltered.filter(r => {
    const haystack = [r.surname, r.firstname, r.othername, r.email, r.phone, r.program, r.matno_found]
      .filter(Boolean).join(' ').toLowerCase()
    return haystack.includes(q)
  })
})

async function fetchData(page = 1, perPage) {
  loading.value = true
  try {
    const res = await adminApi.getForgotMatricRequests(page, perPage ?? pag.perPage)
    const d = res.data
    allItems.value = d.data || d
    if (d.meta || d.current_page) {
      const m = d.meta || d
      pag.currentPage = m.current_page ?? 1
      pag.lastPage = m.last_page ?? 1
      pag.total = m.total ?? 0
      pag.perPage = m.per_page ?? 15
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openTreatModal(item) {
  selectedItem.value = item
  treatForm.value = { retrieve_matno: item.matno_found || '' }
  showModal.value = true
}

async function submitTreat() {
  if (!treatForm.value.retrieve_matno.trim()) return
  treating.value = true
  try {
    await adminApi.treatForgotMatric({ email: selectedItem.value.email, retrieve_matno: treatForm.value.retrieve_matno })
    showModal.value = false
    await fetchData(pag.currentPage)
  } catch (e) {
    console.error(e)
  } finally {
    treating.value = false
  }
}

onMounted(() => fetchData())
</script>
