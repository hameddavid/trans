<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">Generate Transcript (Admin)</h1>

    <div :class="transcriptHtml ? 'grid grid-cols-1 xl:grid-cols-[340px_1fr] gap-6' : 'max-w-md'">
      <!-- Form Panel -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Transcript Details</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Matriculation Number <span class="text-red-500">*</span></label>
            <input v-model="form.matric_number" type="text" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-run-blue focus:border-run-blue" />
            <p v-if="errors.matric_number" class="mt-1 text-xs text-red-500">{{ errors.matric_number[0] }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Transcript Type <span class="text-red-500">*</span></label>
            <select v-model="form.transcript_type" required class="w-full rounded-lg border-gray-300 text-sm focus:ring-run-blue focus:border-run-blue">
              <option value="">Select type</option>
              <option value="OFFICIAL">Official Transcript</option>
              <option value="STUDENT">Student's Copy</option>
            </select>
          </div>

          <div v-if="form.transcript_type === 'OFFICIAL'">
            <label class="block text-xs font-medium text-gray-700 mb-1">Recipient Name</label>
            <input v-model="form.recipient" type="text" class="w-full rounded-lg border-gray-300 text-sm focus:ring-run-blue focus:border-run-blue" />
          </div>

          <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 rounded-lg text-white bg-run-blue hover:bg-run-blue/90 text-sm font-medium disabled:opacity-50">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ loading ? 'Generating...' : 'Generate Transcript' }}
          </button>

          <p v-if="errorMsg" class="text-xs text-red-600 text-center">{{ errorMsg }}</p>
        </form>
      </div>

      <!-- Preview Panel -->
      <div v-if="transcriptHtml" class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
        <!-- Toolbar -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 shrink-0">
          <h2 class="text-sm font-semibold text-gray-900">Preview</h2>
          <div class="flex items-center gap-3">
            <!-- Zoom controls -->
            <div class="flex items-center gap-1.5 bg-gray-100 rounded-lg px-2 py-1">
              <button @click="zoomOut" :disabled="zoom <= 50" class="text-gray-500 hover:text-gray-700 disabled:opacity-30 p-0.5" title="Zoom out">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M20 12H4"/></svg>
              </button>
              <span class="text-[11px] font-mono text-gray-600 w-8 text-center">{{ zoom }}%</span>
              <button @click="zoomIn" :disabled="zoom >= 150" class="text-gray-500 hover:text-gray-700 disabled:opacity-30 p-0.5" title="Zoom in">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              </button>
              <button @click="zoom = 100" class="text-[10px] text-gray-500 hover:text-gray-700 ml-0.5" title="Reset zoom">Reset</button>
            </div>

            <button @click="downloadPdf" :disabled="downloading" class="px-3 py-1.5 text-xs bg-run-gold text-run-blue rounded-lg hover:bg-run-gold/90 font-medium disabled:opacity-50">
              {{ downloading ? 'Downloading...' : 'Download PDF' }}
            </button>
          </div>
        </div>

        <!-- Document Preview -->
        <div class="flex-1 overflow-auto bg-gray-100 p-6" style="max-height: calc(100vh - 200px)">
          <div
            class="transcript-preview mx-auto bg-white shadow-lg border border-gray-300"
            :style="{ transform: `scale(${zoom / 100})`, transformOrigin: 'top center', width: '210mm' }"
            v-html="transcriptHtml"
          ></div>
        </div>
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
const zoom = ref(80)

const form = reactive({
  matric_number: '',
  transcript_type: '',
  recipient: '',
})
const generatedAppId = ref(null)

function zoomIn() {
  if (zoom.value < 150) zoom.value += 10
}
function zoomOut() {
  if (zoom.value > 50) zoom.value -= 10
}

async function handleSubmit() {
  loading.value = true
  errorMsg.value = ''
  errors.value = {}
  transcriptHtml.value = ''
  generatedAppId.value = null

  try {
    const res = await adminApi.submitAdminApp(form)
    transcriptHtml.value = res.data.html || ''
    generatedAppId.value = res.data.app_id || null
    if (!transcriptHtml.value) {
      errorMsg.value = 'Transcript generated but no content returned. The student may have no results.'
    }
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
    const res = await adminApi.downloadAdminApp({ id: generatedAppId.value, transcript_type: form.transcript_type })
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

<style scoped>
.transcript-preview {
  padding: 0;
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 10px;
  color: #1a1a1a;
  line-height: 1.4;
}

.transcript-preview :deep(.header) {
  text-align: center;
  font-family: Arial, Helvetica, sans-serif;
  padding: 12px 0 10px 0;
  width: 100%;
  border-bottom: 2px solid #1a3a6b;
  margin-bottom: 0;
}

.transcript-preview :deep(.header h1) {
  font-size: 22px;
  color: #1a3a6b;
  letter-spacing: 0.5px;
  margin: 0;
  padding: 0;
}

.transcript-preview :deep(.header h2) {
  font-size: 16px;
  color: #1a3a6b;
  margin: 2px 0 0 0;
  padding: 0;
}

.transcript-preview :deep(.header h5) {
  font-style: italic;
  font-size: 10px;
  color: #444;
  font-weight: normal;
  margin: 0;
  padding: 0;
}

.transcript-preview :deep(.header h6) {
  font-style: italic;
  font-size: 8px;
  color: #777;
  font-weight: normal;
  margin: 3px 0 0 0;
  padding: 0;
}

.transcript-preview :deep(.gold-accent) {
  width: 100%;
  height: 3px;
  background-color: #977b1f;
  margin-bottom: 0;
}

.transcript-preview :deep(.header2) {
  border-bottom: 1px solid #999;
  width: 100%;
  padding: 6px 0;
}

.transcript-preview :deep(.header2 table) {
  width: 90%;
  margin: 0 5%;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10px;
}

.transcript-preview :deep(.header2 td) {
  padding: 2px 0;
}

.transcript-preview :deep(.result_table) {
  border-collapse: collapse;
  width: 90%;
  margin: 0 5%;
  font-size: 10px;
  font-family: Georgia, 'Times New Roman', serif;
}

.transcript-preview :deep(.result_table th),
.transcript-preview :deep(.result_table td) {
  padding: 3px 5px;
  font-weight: normal;
  border: 1px solid #ccc;
}

.transcript-preview :deep(.result_table th) {
  font-family: Arial, Helvetica, sans-serif;
  font-weight: bold;
  font-size: 9px;
  background-color: #f0f3f7;
  color: #1a3a6b;
  padding: 5px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.transcript-preview :deep(.result_table2) {
  border-collapse: collapse;
  width: 90%;
  margin: 0 5%;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10px;
}

.transcript-preview :deep(.result_table2 td) {
  padding: 3px 5px;
  font-weight: normal;
  border: 1px solid #ddd;
}

.transcript-preview :deep(.result_table2 caption),
.transcript-preview :deep(caption) {
  text-align: left;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10px;
  padding-top: 14px;
  font-weight: bold;
  color: #1a3a6b;
}

.transcript-preview :deep(.semester-label) {
  border: none !important;
  padding: 14px 5px 4px 0 !important;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10px;
  font-weight: bold;
  color: #1a3a6b;
}

.transcript-preview :deep(.summary-divider) {
  border-top: 1px dotted #999;
  margin: 8px 5% 0 5%;
}

.transcript-preview :deep(.footer_) {
  width: 90%;
  margin-left: 5%;
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 11px;
  font-style: italic;
  margin-top: 20px;
}

.transcript-preview :deep(.footer_4) {
  font-size: 9px;
  font-family: Arial, Helvetica, sans-serif;
  text-align: center;
  color: #888;
  padding: 20px 5%;
  border-top: 1px solid #ddd;
  margin-top: 25px;
}

.transcript-preview :deep(.logo) {
  float: left;
  height: 90px;
  margin-left: 5%;
  margin-top: 0;
}

.transcript-preview :deep(#recipient_h) {
  font-size: 10px;
  font-weight: bold;
  color: #333;
  margin-top: 4px;
}

.transcript-preview :deep(img) {
  max-width: 100%;
}

.transcript-preview :deep(table) {
  border-collapse: collapse;
}
</style>
