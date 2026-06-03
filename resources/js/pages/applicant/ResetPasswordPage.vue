<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Reset Password</h2>
      <p class="mt-1 text-sm text-gray-600">Enter your new password below</p>
    </div>

    <div v-if="success" class="text-center">
      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <p class="text-sm text-gray-700">Your password has been reset successfully.</p>
      <router-link :to="{ name: 'applicant-login' }" class="inline-block mt-4 text-sm text-run-blue font-medium hover:underline">
        Go to Login
      </router-link>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-5">
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          minlength="6"
          placeholder="Minimum 6 characters"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
        />
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
        />
      </div>

      <p v-if="mismatch" class="text-sm text-red-500">Passwords do not match</p>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <button
        type="submit"
        :disabled="loading || mismatch"
        class="w-full bg-run-blue text-white py-2.5 rounded-lg font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
      >
        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        {{ loading ? 'Resetting...' : 'Reset Password' }}
      </button>
    </form>

    <p v-if="!success" class="mt-5 text-center text-sm text-gray-600">
      <router-link :to="{ name: 'applicant-login' }" class="text-run-blue font-medium hover:underline">Back to Login</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import * as applicantApi from '@/api/applicantApi';

const route = useRoute();

const form = ref({
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const success = ref(false);
const errorMessage = ref('');

const mismatch = computed(() => form.value.password && form.value.password_confirmation && form.value.password !== form.value.password_confirmation);

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await applicantApi.resetPasswordWithToken({
      email: route.query.email,
      token: route.query.token,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });
    success.value = true;
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors;
      errorMessage.value = errs ? Object.values(errs).flat().join(' ') : e.response.data.message;
    } else {
      errorMessage.value = e.response?.data?.message || 'Failed to reset password. The link may have expired.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
