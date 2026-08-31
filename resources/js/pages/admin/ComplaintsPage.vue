<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">Student Complaints</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-run-blue" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </div>
      <div v-else-if="complaints.length === 0" class="text-center py-12 text-gray-500">No complaints found</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Student</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Matric No.</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Subject</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider sticky-action-col">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(c, i) in complaints" :key="c.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 text-xs text-gray-500">{{ rowNumber(i) }}</td>
            <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(c.created_at) }}</td>
            <td class="px-4 py-2 text-xs font-medium text-gray-900 whitespace-nowrap">{{ c.applicant?.surname }} {{ c.applicant?.firstname }}</td>
            <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">{{ c.matric_number }}</td>
            <td class="px-4 py-2 text-xs text-gray-700">{{ c.subject }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <span :class="c.status === 'RESOLVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium">
                {{ c.status }}
              </span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap sticky-action-col">
              <button @click="openComplaint(c)" class="text-run-blue hover:underline text-xs font-medium">
                {{ c.status === 'PENDING' ? 'Respond' : 'View' }}
              </button>
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

    <!-- Complaint Detail Modal -->
    <div v-if="selected" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="selected = null" />
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-4 z-10 max-h-[90vh] overflow-y-auto">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Complaint Details</h3>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Student:</span>
            <span class="font-medium text-gray-900">{{ selected.applicant?.surname }} {{ selected.applicant?.firstname }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Matric Number:</span>
            <span class="font-medium text-gray-900">{{ selected.matric_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Email:</span>
            <span class="font-medium text-gray-900">{{ selected.applicant?.email }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Date:</span>
            <span class="font-medium text-gray-900">{{ formatDate(selected.created_at) }}</span>
          </div>
          <div>
            <p class="text-gray-500 mb-1">Subject:</p>
            <p class="font-medium text-gray-900">{{ selected.subject }}</p>
          </div>
          <div>
            <p class="text-gray-500 mb-1">Message:</p>
            <p class="text-gray-800 bg-gray-50 rounded-lg p-3 whitespace-pre-wrap">{{ selected.message }}</p>
          </div>

          <div v-if="selected.attachment">
            <p class="text-gray-500 mb-1">Attachment:</p>
            <button @click="downloadAttachment(selected)" class="inline-flex items-center gap-1.5 text-sm text-run-blue hover:underline font-medium">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
              Download Attachment
            </button>
          </div>

          <div v-if="selected.status === 'RESOLVED'" class="border-t pt-3 mt-3">
            <p class="text-gray-500 mb-1">Admin Response:</p>
            <p class="text-gray-800 bg-green-50 rounded-lg p-3 whitespace-pre-wrap">{{ selected.admin_response }}</p>
            <p class="text-xs text-gray-400 mt-2">Responded by {{ selected.responded_by }} on {{ formatDate(selected.responded_at) }}</p>
          </div>

          <div v-else class="border-t pt-3 mt-3 space-y-3">
            <div>
              <button @click="showEditFields = !showEditFields" class="text-sm font-medium text-run-blue hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ showEditFields ? 'Hide' : 'Edit' }} Student Details
              </button>
            </div>

            <div v-if="showEditFields" class="bg-blue-50 border border-blue-200 rounded-lg p-3 space-y-2">
              <p class="text-xs text-blue-600 font-medium mb-2">Update student record (changes are optional)</p>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Surname</label>
                  <input v-model="editFields.surname" type="text" class="w-full text-sm rounded-md border-gray-300 focus:ring-run-blue focus:border-run-blue" />
                </div>
                <div>
                  <label class="block text-[11px] font-medium text-gray-600 mb-0.5">First Name</label>
                  <input v-model="editFields.firstname" type="text" class="w-full text-sm rounded-md border-gray-300 focus:ring-run-blue focus:border-run-blue" />
                </div>
                <div>
                  <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Email</label>
                  <input v-model="editFields.email" type="email" class="w-full text-sm rounded-md border-gray-300 focus:ring-run-blue focus:border-run-blue" />
                </div>
                <div>
                  <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Phone</label>
                  <input v-model="editFields.mobile" type="text" class="w-full text-sm rounded-md border-gray-300 focus:ring-run-blue focus:border-run-blue" />
                </div>
                <div class="col-span-2">
                  <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Matric Number</label>
                  <input v-model="editFields.matric_number" type="text" class="w-full text-sm rounded-md border-gray-300 focus:ring-run-blue focus:border-run-blue" />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Your Response</label>
              <textarea
                v-model="responseText"
                rows="4"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
                placeholder="Type your response to the student..."
              />
            </div>
            <div v-if="responseError" class="text-sm text-red-600">{{ responseError }}</div>
            <div class="flex justify-end gap-3">
              <button @click="selected = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
              <button @click="submitResponse" :disabled="responding || !responseText" class="px-4 py-2 text-sm font-medium text-white bg-run-blue rounded-lg hover:bg-run-blue/90 disabled:opacity-50">
                {{ responding ? 'Sending...' : 'Send Response' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import * as adminApi from '@/api/adminApi'
import AppPagination from '@/components/common/AppPagination.vue'

const toast = useToast()
const complaints = ref([])
const loading = ref(true)
const selected = ref(null)
const responseText = ref('')
const responding = ref(false)
const responseError = ref('')
const showEditFields = ref(false)
const editFields = reactive({ surname: '', firstname: '', email: '', mobile: '', matric_number: '' })
const pag = reactive({ currentPage: 1, lastPage: 1, total: 0, perPage: 15 })

const rowNumber = (index) => (pag.currentPage - 1) * pag.perPage + index + 1

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' })
}

function openComplaint(c) {
  selected.value = c
  responseText.value = ''
  responseError.value = ''
  showEditFields.value = false
  const a = c.applicant || {}
  editFields.surname = a.surname || ''
  editFields.firstname = a.firstname || ''
  editFields.email = a.email || ''
  editFields.mobile = a.mobile || ''
  editFields.matric_number = c.matric_number || ''
}

async function downloadAttachment(c) {
  try {
    const res = await adminApi.downloadComplaintAttachment(c.id)
    const disposition = res.headers['content-disposition'] || ''
    const match = disposition.match(/filename="?(.+?)"?$/i)
    const filename = match ? match[1] : c.attachment.split('/').pop()
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    a.remove()
  } catch (e) {
    toast.error('Failed to download attachment.')
  }
}

async function submitResponse() {
  responding.value = true
  responseError.value = ''
  try {
    const payload = { id: selected.value.id, admin_response: responseText.value }
    if (showEditFields.value) {
      const a = selected.value.applicant || {}
      const changed = {}
      if (editFields.surname && editFields.surname !== (a.surname || '')) changed.surname = editFields.surname
      if (editFields.firstname && editFields.firstname !== (a.firstname || '')) changed.firstname = editFields.firstname
      if (editFields.email && editFields.email !== (a.email || '')) changed.email = editFields.email
      if (editFields.mobile !== (a.mobile || '')) changed.mobile = editFields.mobile
      if (editFields.matric_number && editFields.matric_number !== (selected.value.matric_number || '')) changed.matric_number = editFields.matric_number
      if (Object.keys(changed).length) payload.update_fields = changed
    }
    await adminApi.respondToComplaint(payload)
    selected.value.status = 'RESOLVED'
    selected.value.admin_response = responseText.value
    toast.success('Response sent and applicant notified by email.')
    selected.value = null
    await fetchData(pag.currentPage)
  } catch (e) {
    responseError.value = e.response?.data?.message || 'Failed to send response.'
  } finally {
    responding.value = false
  }
}

async function fetchData(page = 1, perPage) {
  loading.value = true
  try {
    const res = await adminApi.getComplaints(page, perPage ?? pag.perPage)
    const d = res.data
    complaints.value = d.data || d
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
