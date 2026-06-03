<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="text-lg font-bold text-gray-900 mb-4">Generate Transcript (Admin)</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Transcript Details</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Matriculation Number <span class="text-red-500">*</span></label>
            <input v-model="form.matric_number" type="text" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
            <p v-if="errors.matric_number" class="mt-1 text-sm text-red-500">{{ errors.matric_number[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Transcript Type <span class="text-red-500">*</span></label>
            <select v-model="form.transcript_type" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue">
              <option value="">Select type</option>
              <option value="OFFICIAL">Official Transcript</option>
              <option value="STUDENT">Student's Copy</option>
            </select>
          </div>

          <div v-if="form.transcript_type === 'OFFICIAL'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
            <input v-model="form.recipient" type="text" class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          </div>

          <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 rounded-lg text-white bg-run-blue hover:bg-run-blue/90 font-medium disabled:opacity-50">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ loading ? 'Generating...' : 'Generate Transcript' }}
          </button>

          <p v-if="errorMsg" class="text-sm text-red-600 text-center">{{ errorMsg }}</p>
        </form>
      </div>

      <div v-if="transcriptHtml" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Preview</h2>
          <button @click="downloadPdf" :disabled="downloading" class="px-4 py-2 text-sm bg-run-gold text-run-blue rounded-lg hover:bg-run-gold/90 disabled:opacity-50">
            {{ downloading ? 'Downloading...' : 'Download PDF' }}
          </button>
        </div>
        <div class="border rounded-lg p-4 overflow-auto max-h-[600px]" v-html="transcriptHtml"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import * as adminApi from '@/api/adminApi'

const loading = ref(false)
const downloading = ref(false)
const errorMsg = ref('')
const errors = ref({})
const transcriptHtml = ref('')

const form = reactive({
  matric_number: '',
  transcript_type: '',
  recipient: '',
})

async function handleSubmit() {
  loading.value = true
  errorMsg.value = ''
  errors.value = {}
  transcriptHtml.value = ''

  try {
    const res = await adminApi.submitAdminApp(form)
    transcriptHtml.value = res.data.html || res.data.result || ''
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      errorMsg.value = err.response?.data?.message || 'An error occurred'
    }
  } finally {
    loading.value = false
  }
}

async function downloadPdf() {
  downloading.value = true
  try {
    const res = await adminApi.downloadAdminApp({ matric_number: form.matric_number, transcript_type: form.transcript_type, recipient: form.recipient })
    const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `transcript_${form.matric_number}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    errorMsg.value = 'Failed to download PDF'
  } finally {
    downloading.value = false
  }
}
</script>
