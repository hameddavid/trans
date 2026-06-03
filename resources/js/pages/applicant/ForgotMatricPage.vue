<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Forgot Matric Number</h2>
      <p class="mt-1 text-sm text-gray-600">Provide your details and we'll help you retrieve your matric number</p>
    </div>

    <div v-if="success" class="text-center">
      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <p class="text-sm text-gray-700">Your request has been submitted. The admin team will review it and contact you via email.</p>
      <router-link :to="{ name: 'applicant-login' }" class="inline-block mt-4 text-sm text-run-blue font-medium hover:underline">
        Back to Login
      </router-link>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="surname" class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
          <input
            id="surname"
            v-model="form.surname"
            type="text"
            required
            placeholder="Surname"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
          />
          <p v-if="errors.surname" class="mt-1 text-xs text-red-600">{{ errors.surname[0] }}</p>
        </div>

        <div>
          <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
          <input
            id="firstname"
            v-model="form.firstname"
            type="text"
            required
            placeholder="First name"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
          />
          <p v-if="errors.firstname" class="mt-1 text-xs text-red-600">{{ errors.firstname[0] }}</p>
        </div>
      </div>

      <div>
        <label for="othername" class="block text-sm font-medium text-gray-700 mb-1">Other Name <span class="text-gray-400">(optional)</span></label>
        <input
          id="othername"
          v-model="form.othername"
          type="text"
          placeholder="Other name"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
        />
      </div>

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

      <div>
        <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Programme of Study</label>
        <select
          id="program"
          v-model="form.program"
          required
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
        >
          <option value="">Select programme</option>
          <option v-for="prog in programmes" :key="prog.programme" :value="prog.programme">
            {{ prog.programme }}
          </option>
        </select>
        <p v-if="errors.program" class="mt-1 text-xs text-red-600">{{ errors.program[0] }}</p>
      </div>

      <div>
        <label for="date_left" class="block text-sm font-medium text-gray-700 mb-1">Year of Graduation</label>
        <select
          id="date_left"
          v-model="form.date_left"
          required
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
        >
          <option value="">Select year</option>
          <option v-for="year in graduationYears" :key="year" :value="String(year)">
            {{ year }}
          </option>
        </select>
        <p v-if="errors.date_left" class="mt-1 text-xs text-red-600">{{ errors.date_left[0] }}</p>
      </div>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
        {{ errorMessage }}
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
        {{ loading ? 'Submitting...' : 'Submit Request' }}
      </button>
    </form>

    <p v-if="!success" class="mt-5 text-center text-sm text-gray-600">
      <router-link :to="{ name: 'applicant-login' }" class="text-run-blue font-medium hover:underline">Back to Login</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import * as applicantApi from '@/api/applicantApi';
import * as publicApi from '@/api/publicApi';

const form = ref({
  surname: '',
  firstname: '',
  othername: '',
  email: '',
  phone: '',
  program: '',
  date_left: '',
});

const loading = ref(false);
const success = ref(false);
const errorMessage = ref('');
const errors = ref({});
const programmes = ref([]);

const currentYear = new Date().getFullYear();
const graduationYears = computed(() => {
  const years = [];
  for (let y = currentYear; y >= 2005; y--) {
    years.push(y);
  }
  return years;
});

onMounted(async () => {
  try {
    const { data } = await publicApi.getProgrammeList();
    programmes.value = data;
  } catch {}
});

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    await applicantApi.forgotMatric(form.value);
    success.value = true;
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || 'Please correct the errors below.';
    } else {
      errorMessage.value = e.response?.data?.message || 'Failed to submit request. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
