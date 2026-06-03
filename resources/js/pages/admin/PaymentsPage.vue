<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">All Payments</h1>
      <input v-model="search" type="text" placeholder="Search payments..." class="rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue text-sm w-64" />
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-run-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </div>
      <div v-else-if="filtered.length === 0" class="text-center py-12 text-gray-500">No payments found</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">RRR</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Amount</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, i) in filtered" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ rowNumber(i) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ item.applicant_name || item.user_id }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.rrr }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ Number(item.amount).toLocaleString() }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.type || 'Transcript' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium"
                    :class="item.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                {{ item.status }}
              </span>
            </td>
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

const payments = ref([])
const loading = ref(true)
const search = ref('')
const pag = reactive({ currentPage: 1, lastPage: 1, total: 0, perPage: 15 })

const rowNumber = (index) => (pag.currentPage - 1) * pag.perPage + index + 1

const filtered = computed(() => {
  if (!search.value) return payments.value
  const q = search.value.toLowerCase()
  return payments.value.filter(p =>
    (p.rrr + ' ' + (p.applicant_name || '') + ' ' + p.amount).toLowerCase().includes(q)
  )
})

async function fetchData(page = 1, perPage) {
  loading.value = true
  try {
    const res = await adminApi.getPayments(page, perPage ?? pag.perPage)
    const d = res.data
    payments.value = d.data || d
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

onMounted(() => fetchData())
</script>
