<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Admin Registration</h2>
      <p class="mt-1 text-sm text-gray-600">Create an administrator account</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              placeholder="Full name"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition"
            />
            <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              placeholder="admin@run.edu.ng"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition"
            />
            <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              id="role"
              v-model="form.role"
              required
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition bg-white"
            >
              <option value="" disabled>Select a role</option>
              <option value="200">Recommender</option>
              <option value="300">Approver</option>
            </select>
            <p v-if="errors.role" class="mt-1 text-xs text-red-600">{{ errors.role[0] }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              minlength="6"
              placeholder="Minimum 6 characters"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none transition"
            />
            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password[0] }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              placeholder="Re-enter password"
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
            {{ loading ? 'Creating Account...' : 'Register' }}
          </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-600">
      Already have an account?
      <router-link :to="{ name: 'admin-login' }" class="text-run-dark font-medium hover:underline">Login here</router-link>
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
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');
const errors = ref({});

async function handleSubmit() {
  errorMessage.value = '';
  errors.value = {};

  if (form.value.password !== form.value.password_confirmation) {
    errorMessage.value = 'Passwords do not match.';
    return;
  }

  loading.value = true;

  try {
    await adminAuthStore.register(form.value);
    router.push({ name: 'admin-login' });
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || 'Please correct the errors below.';
    } else {
      errorMessage.value = e.response?.data?.message || 'Registration failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
