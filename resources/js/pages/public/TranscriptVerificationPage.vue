<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Transcript Verification</h1>
        <p class="mt-2 text-gray-600">
          Enter the verification code printed on the transcript and the student's matriculation number to verify its authenticity.
        </p>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="used_token" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
            <input
              id="used_token"
              v-model="form.used_token"
              type="text"
              required
              placeholder="Enter the code printed on the transcript"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
          </div>

          <div>
            <label for="matno" class="block text-sm font-medium text-gray-700 mb-1">Matriculation Number</label>
            <input
              id="matno"
              v-model="form.matno"
              type="text"
              required
              placeholder="e.g. RUN/2019/001"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-run-blue text-white py-2.5 rounded-lg font-semibold hover:bg-run-blue/90 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Verifying...' : 'Verify Transcript' }}
          </button>
        </form>
      </div>

      <div v-if="error" class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-sm">
        {{ error }}
      </div>

      <!-- Verification Result -->
      <div v-if="result" class="mt-6 select-none" style="-webkit-user-select: none; -moz-user-select: none; user-select: none;">
        <!-- Verified Banner -->
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-green-800">Transcript Verified</h2>
              <p class="text-sm text-green-700">This transcript is authentic and was issued by Redeemer's University, Ede.</p>
            </div>
          </div>
        </div>

        <!-- Verification Certificate Card -->
        <div class="bg-white rounded-xl shadow-sm border-2 border-run-blue/20 overflow-hidden">
          <!-- Header -->
          <div class="bg-run-blue px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-1">
              <img src="/assets/images/run_logo.png" alt="RUN Logo" class="h-8 w-8" />
              <h3 class="text-white font-bold text-lg">Redeemer's University, Ede</h3>
            </div>
            <p class="text-run-gold text-sm font-medium">Transcript Verification Certificate</p>
          </div>

          <!-- Watermark wrapper -->
          <div class="relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
              <img src="/assets/images/run_logo_big.png" alt="" class="w-64 h-64 object-contain" />
            </div>

            <div class="relative px-6 py-5 space-y-0">
              <!-- Student Info -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Student Name</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.student_name }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Matriculation Number</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.matric_number }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Programme</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.programme }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Department</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.department }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Faculty</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.faculty }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Qualification</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.qualification || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Class of Degree</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.class_of_degree || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">CGPA</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.cgpa || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Year of Graduation</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.graduation_year || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Transcript Type</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.transcript_type || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Intended Recipient</p>
                  <p class="text-sm font-semibold text-gray-900">{{ result.recipient || '-' }}</p>
                </div>
                <div class="py-3 border-b border-gray-100">
                  <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Date Approved</p>
                  <p class="text-sm font-semibold text-gray-900">{{ formatDate(result.approved_at) }}</p>
                </div>
              </div>
            </div>

            <!-- Footer disclaimer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
              <p class="text-xs text-gray-400 text-center leading-relaxed">
                This verification confirms that the transcript bearing the above details was issued by Redeemer's University, Ede.
                This result is for verification purposes only and does not constitute a replacement transcript.
                For enquiries, contact <span class="text-gray-500">transcript@run.edu.ng</span>
              </p>
              <p class="text-xs text-gray-300 text-center mt-2">
                Verified on {{ new Date().toLocaleDateString('en-NG', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import * as publicApi from '@/api/publicApi';

const form = ref({
  used_token: '',
  matno: '',
});

const loading = ref(false);
const error = ref('');
const result = ref(null);

function formatDate(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('en-NG', { year: 'numeric', month: 'long', day: 'numeric' });
}

async function handleSubmit() {
  loading.value = true;
  error.value = '';
  result.value = null;

  try {
    const { data } = await publicApi.verifyTranscript(form.value);
    result.value = data.data;
  } catch (e) {
    const msg = e.response?.data?.message || 'Verification failed. Please check the details and try again.';
    error.value = msg;
  } finally {
    loading.value = false;
  }
}
</script>
