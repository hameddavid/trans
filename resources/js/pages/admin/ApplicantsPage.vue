<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">All Applicants</h1>
      <input v-model="search" type="text" placeholder="Search applicants..." class="rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue text-sm w-64" />
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-run-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </div>
      <div v-else-if="filtered.length === 0" class="text-center py-12 text-gray-500">No applicants found</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, i) in filtered" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ rowNumber(i) }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ item.surname }} {{ item.firstname }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.email }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.matric_number }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.mobile }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ item.created_at }}</td>
            <td class="px-4 py-2 whitespace-nowrap sticky-action-col">
              <button @click="openEdit(item)" class="text-run-blue hover:underline text-sm font-medium">Edit</button>
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

    <!-- Edit Modal -->
    <div v-if="editItem" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="editItem = null" />
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-4 z-10">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Edit Applicant</h3>
        <p class="text-sm text-gray-500 mb-4">Matric Number: <span class="font-medium text-gray-900">{{ editItem.matric_number }}</span></p>

        <form @submit.prevent="saveEdit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
            <input v-model="editForm.surname" type="text" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <input v-model="editForm.firstname" type="text" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="editForm.email" type="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="editForm.mobile" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue" />
          </div>

          <div v-if="editError" class="text-sm text-red-600">{{ editError }}</div>

          <div class="flex justify-end gap-3">
            <button type="button" @click="editItem = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-run-blue rounded-lg hover:bg-run-blue/90 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import * as adminApi from '@/api/adminApi'
import AppPagination from '@/components/common/AppPagination.vue'

const toast = useToast()
const applicants = ref([])
const loading = ref(true)
const search = ref('')
const pag = reactive({ currentPage: 1, lastPage: 1, total: 0, perPage: 15 })

const editItem = ref(null)
const editForm = reactive({ surname: '', firstname: '', email: '', mobile: '' })
const saving = ref(false)
const editError = ref('')

const rowNumber = (index) => (pag.currentPage - 1) * pag.perPage + index + 1

const filtered = computed(() => {
  if (!search.value) return applicants.value
  const q = search.value.toLowerCase()
  return applicants.value.filter(a =>
    (a.surname + ' ' + a.firstname + ' ' + a.email + ' ' + a.matric_number).toLowerCase().includes(q)
  )
})

function openEdit(item) {
  editItem.value = item
  editForm.surname = item.surname
  editForm.firstname = item.firstname
  editForm.email = item.email
  editForm.mobile = item.mobile || ''
  editError.value = ''
}

async function saveEdit() {
  saving.value = true
  editError.value = ''
  try {
    await adminApi.updateApplicant({
      id: editItem.value.id,
      surname: editForm.surname,
      firstname: editForm.firstname,
      email: editForm.email,
      mobile: editForm.mobile,
    })
    editItem.value.surname = editForm.surname
    editItem.value.firstname = editForm.firstname
    editItem.value.email = editForm.email
    editItem.value.mobile = editForm.mobile
    toast.success('Applicant updated. Email notification sent.')
    editItem.value = null
  } catch (e) {
    editError.value = e.response?.data?.message || 'Failed to update applicant.'
  } finally {
    saving.value = false
  }
}

async function fetchData(page = 1, perPage) {
  loading.value = true
  try {
    const res = await adminApi.getApplicants(page, perPage ?? pag.perPage)
    const d = res.data
    applicants.value = d.data || d
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
