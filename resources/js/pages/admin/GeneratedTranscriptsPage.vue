<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Generated Transcripts</h1>
      <input v-model="search" type="text" placeholder="Search..." class="rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue text-sm w-64" />
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-run-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </div>
      <div v-else-if="filtered.length === 0" class="text-center py-12 text-gray-500">No generated transcripts found</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, i) in filtered" :key="item.id || i" class="hover:bg-gray-50">
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ rowNumber(i) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ item.SURNAME }} {{ item.FIRSTNAME }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.matric_number }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.transcript_type }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.recipient }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.created_at }}</td>
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import * as adminApi from '@/api/adminApi'
import AppPagination from '@/components/common/AppPagination.vue'

const transcripts = ref([])
const loading = ref(true)
const search = ref('')
const pag = reactive({ currentPage: 1, lastPage: 1, total: 0, perPage: 15 })

const rowNumber = (index) => (pag.currentPage - 1) * pag.perPage + index + 1

const filtered = computed(() => {
  if (!search.value) return transcripts.value
  const q = search.value.toLowerCase()
  return transcripts.value.filter(t =>
    ((t.SURNAME || '') + ' ' + (t.FIRSTNAME || '') + ' ' + t.matric_number).toLowerCase().includes(q)
  )
})

async function fetchData(page = 1, perPage) {
  loading.value = true
  try {
    const res = await adminApi.getGeneratedTranscripts(page, perPage ?? pag.perPage)
    const d = res.data
    transcripts.value = d.data || d
    if (d.current_page) {
      pag.currentPage = d.current_page
      pag.lastPage = d.last_page ?? 1
      pag.total = d.total ?? 0
      pag.perPage = d.per_page ?? 15
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchData())
</script>
