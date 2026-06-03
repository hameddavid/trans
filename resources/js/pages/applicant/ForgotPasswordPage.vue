<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Forgot Password</h2>
      <p class="mt-1 text-sm text-gray-600">Enter your email to receive a password reset link</p>
    </div>

    <div>
        <div v-if="success" class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <p class="text-sm text-gray-700">A password reset link has been sent to your email address.</p>
          <router-link :to="{ name: 'applicant-login' }" class="inline-block mt-4 text-sm text-run-blue font-medium hover:underline">
            Back to Login
          </router-link>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="you@example.com"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
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
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>
    </div>

    <p class="mt-5 text-center text-sm text-gray-600">
      <router-link :to="{ name: 'applicant-login' }" class="text-run-blue font-medium hover:underline">Back to Login</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const form = ref({
  email: '',
});

const loading = ref(false);
const success = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.forgotPassword(form.value);
    success.value = true;
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors;
      errorMessage.value = errs ? Object.values(errs).flat().join(' ') : e.response.data.message;
    } else {
      errorMessage.value = e.response?.data?.message || 'Failed to send reset link. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
