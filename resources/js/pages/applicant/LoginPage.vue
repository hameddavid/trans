<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Applicant Login</h2>
      <p class="mt-1 text-sm text-gray-600">Sign in to access your transcript portal</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="matno" class="block text-sm font-medium text-gray-700 mb-1">Matric Number</label>
            <input
              id="matno"
              v-model="form.matno"
              type="text"
              required
              autocomplete="username"
              placeholder="e.g. RUN/CMP/21/10851"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
                <EyeIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <div class="flex justify-between">
            <router-link :to="{ name: 'applicant-forgot-matric' }" class="text-sm text-gray-500 hover:text-run-blue hover:underline">
              Forgot Matric Number?
            </router-link>
            <router-link :to="{ name: 'applicant-forgot-password' }" class="text-sm text-run-blue hover:underline">
              Forgot Password?
            </router-link>
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
            {{ loading ? 'Signing in...' : 'Login' }}
          </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-600">
      Don't have an account?
      <router-link :to="{ name: 'applicant-register' }" class="text-run-blue font-medium hover:underline">Register here</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const showPassword = ref(false);

const form = ref({
  matno: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login(form.value);
    router.push({ name: 'applicant-dashboard' });
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
