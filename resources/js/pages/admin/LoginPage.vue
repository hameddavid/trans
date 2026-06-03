<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Admin Portal</h2>
      <p class="mt-1 text-sm text-gray-600">Sign in to the administration panel</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="admin@run.edu.ng"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="Enter your password"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition"
            />
          </div>

          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-run-dark text-white py-2.5 rounded-lg font-semibold hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ loading ? 'Signing in...' : 'Login' }}
          </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-600">
      Need an admin account?
      <router-link :to="{ name: 'admin-register' }" class="text-run-dark font-medium hover:underline">Admin Registration</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAdminAuthStore } from '@/stores/adminAuth';

const router = useRouter();
const adminAuthStore = useAdminAuthStore();

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await adminAuthStore.login(form.value);
    router.push({ name: 'admin-dashboard' });
  } catch (e) {
    if (e.response?.status === 422) {
      const errs = e.response.data.errors;
      errorMessage.value = errs ? Object.values(errs).flat().join(' ') : e.response.data.message;
    } else {
      errorMessage.value = e.response?.data?.message || 'Login failed. Please check your credentials.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
