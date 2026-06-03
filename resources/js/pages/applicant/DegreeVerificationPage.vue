<template>
  <div class="max-w-2xl mx-auto">
    <h1 class="text-lg font-bold text-gray-900 mb-4">Degree Verification Request</h1>

    <div v-if="submitted" class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-run-gold" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h2 class="mt-4 text-lg font-semibold text-green-800">Request Submitted Successfully</h2>
      <p class="mt-2 text-green-600">Your degree verification request has been submitted. You will be contacted via email.</p>
      <button @click="resetForm" class="mt-6 px-4 py-2 bg-run-blue text-white rounded-lg hover:bg-run-blue/90">Submit Another Request</button>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Requesting Organization <span class="text-red-500">*</span></label>
        <input v-model="form.organization" type="text" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
        <p v-if="errors.organization" class="mt-1 text-sm text-red-500">{{ errors.organization[0] }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email <span class="text-red-500">*</span></label>
          <input v-model="form.email" type="email" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone <span class="text-red-500">*</span></label>
          <input v-model="form.phone" type="tel" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.phone" class="mt-1 text-sm text-red-500">{{ errors.phone[0] }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Graduate's Matric Number <span class="text-red-500">*</span></label>
          <input v-model="form.matric_number" type="text" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.matric_number" class="mt-1 text-sm text-red-500">{{ errors.matric_number[0] }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Graduate's Full Name <span class="text-red-500">*</span></label>
          <input v-model="form.graduate_name" type="text" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.graduate_name" class="mt-1 text-sm text-red-500">{{ errors.graduate_name[0] }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Year of Graduation <span class="text-red-500">*</span></label>
        <input v-model="form.year_of_graduation" type="text" required placeholder="e.g. 2020" class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
        <p v-if="errors.year_of_graduation" class="mt-1 text-sm text-red-500">{{ errors.year_of_graduation[0] }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Document</label>
        <input type="file" @change="handleFile" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-run-blue/10 file:text-run-blue hover:file:bg-run-blue/20" />
        <p class="mt-1 text-xs text-gray-400">PDF, JPG, PNG (max 5MB)</p>
      </div>

      <div class="pt-4">
        <button type="submit" :disabled="loading" class="w-full flex justify-center py-2.5 px-4 rounded-lg text-white bg-run-blue hover:bg-run-blue/90 font-medium disabled:opacity-50">
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          {{ loading ? 'Submitting...' : 'Submit Verification Request' }}
        </button>
      </div>

      <p v-if="errorMsg" class="text-sm text-red-600 text-center">{{ errorMsg }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import * as applicantApi from '@/api/applicantApi'

const loading = ref(false)
const submitted = ref(false)
const errorMsg = ref('')
const errors = ref({})
const file = ref(null)

const form = reactive({
  organization: '',
  email: '',
  phone: '',
  matric_number: '',
  graduate_name: '',
  year_of_graduation: '',
})

function handleFile(e) {
  file.value = e.target.files[0] || null
}

async function handleSubmit() {
  loading.value = true
  errorMsg.value = ''
  errors.value = {}

  try {
    const formData = new FormData()
    Object.entries(form).forEach(([key, val]) => formData.append(key, val))
    if (file.value) formData.append('document', file.value)

    await applicantApi.submitDegreeVerification(formData)
    submitted.value = true
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

function resetForm() {
  Object.keys(form).forEach(k => form[k] = '')
  file.value = null
  submitted.value = false
}
</script>
