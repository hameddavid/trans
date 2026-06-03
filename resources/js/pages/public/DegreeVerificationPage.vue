<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Degree Verification</h1>
        <p class="mt-2 text-gray-600">
          For institutions seeking to verify the authenticity of degrees awarded by Redeemer's University
        </p>
      </div>

      <div v-if="success" class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-lg font-semibold text-green-800 mb-1">Request Submitted</h2>
        <p class="text-sm text-green-700">Your verification request has been submitted. You will be contacted via email.</p>
        <button @click="resetForm" class="mt-4 text-sm text-run-blue hover:underline">Submit another request</button>
      </div>

      <div v-else class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="applicant_name" class="block text-sm font-medium text-gray-700 mb-1">Applicant's Full Name</label>
            <input
              id="applicant_name"
              v-model="form.applicant_name"
              type="text"
              required
              placeholder="Your full name"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.applicant_name" class="mt-1 text-xs text-red-600">{{ errors.applicant_name[0] }}</p>
          </div>

          <div>
            <label for="organization" class="block text-sm font-medium text-gray-700 mb-1">Organization/Institution Name</label>
            <input
              id="organization"
              v-model="form.organization"
              type="text"
              required
              placeholder="Name of your organization"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.organization" class="mt-1 text-xs text-red-600">{{ errors.organization[0] }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                placeholder="you@example.com"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                required
                placeholder="+234..."
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <p v-if="errors.phone" class="mt-1 text-xs text-red-600">{{ errors.phone[0] }}</p>
            </div>
          </div>

          <div>
            <label for="degree_holder_matric" class="block text-sm font-medium text-gray-700 mb-1">Matriculation Number of Degree Holder</label>
            <input
              id="degree_holder_matric"
              v-model="form.degree_holder_matric"
              type="text"
              required
              placeholder="e.g. RUN/2019/001"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.degree_holder_matric" class="mt-1 text-xs text-red-600">{{ errors.degree_holder_matric[0] }}</p>
          </div>

          <div>
            <label for="degree_holder_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name of Degree Holder</label>
            <input
              id="degree_holder_name"
              v-model="form.degree_holder_name"
              type="text"
              required
              placeholder="Full name as on the degree"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.degree_holder_name" class="mt-1 text-xs text-red-600">{{ errors.degree_holder_name[0] }}</p>
          </div>

          <div>
            <label for="year_of_graduation" class="block text-sm font-medium text-gray-700 mb-1">Year of Graduation</label>
            <input
              id="year_of_graduation"
              v-model="form.year_of_graduation"
              type="text"
              required
              placeholder="e.g. 2023"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.year_of_graduation" class="mt-1 text-xs text-red-600">{{ errors.year_of_graduation[0] }}</p>
          </div>

          <div>
            <label for="document" class="block text-sm font-medium text-gray-700 mb-1">Upload Supporting Document</label>
            <input
              id="document"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleFile"
              class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-run-blue/10 file:text-run-blue hover:file:bg-run-blue/20 transition"
            />
            <p class="mt-1 text-xs text-gray-400">Accepted formats: PDF, JPG, PNG</p>
            <p v-if="errors.document" class="mt-1 text-xs text-red-600">{{ errors.document[0] }}</p>
          </div>

          <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
            <p class="text-sm text-blue-800">
              A processing fee may apply for degree verification requests. Payment details will be sent to your email after your request is reviewed.
            </p>
          </div>

          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-run-blue text-white py-2.5 rounded-lg font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Submitting...' : 'Submit Verification Request' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import * as publicApi from '@/api/publicApi';

const form = ref({
  applicant_name: '',
  organization: '',
  email: '',
  phone: '',
  degree_holder_matric: '',
  degree_holder_name: '',
  year_of_graduation: '',
});

const file = ref(null);
const loading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});

function handleFile(e) {
  file.value = e.target.files[0] || null;
}

function resetForm() {
  form.value = {
    applicant_name: '',
    organization: '',
    email: '',
    phone: '',
    degree_holder_matric: '',
    degree_holder_name: '',
    year_of_graduation: '',
  };
  file.value = null;
  success.value = false;
  errorMessage.value = '';
  errors.value = {};
}

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    const formData = new FormData();
    Object.entries(form.value).forEach(([key, val]) => {
      formData.append(key, val);
    });
    if (file.value) {
      formData.append('document', file.value);
    }

    await publicApi.verifyDegree(formData);
    success.value = true;
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || 'Please correct the errors below.';
    } else {
      errorMessage.value = e.response?.data?.message || 'An error occurred. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
