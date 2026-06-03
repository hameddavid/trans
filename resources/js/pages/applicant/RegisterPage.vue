<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-bold text-gray-900">Create Account</h2>
      <p class="mt-1 text-sm text-gray-600">Register to apply for transcripts and verifications</p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                placeholder="First name"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <p v-if="errors.first_name" class="mt-1 text-xs text-red-600">{{ errors.first_name[0] }}</p>
            </div>
          </div>

          <div>
            <label for="other_name" class="block text-sm font-medium text-gray-700 mb-1">Other Name <span class="text-gray-400">(optional)</span></label>
            <input
              id="other_name"
              v-model="form.other_name"
              type="text"
              placeholder="Other name"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
          </div>

          <div>
            <label for="matric_number" class="block text-sm font-medium text-gray-700 mb-1">Matriculation Number</label>
            <input
              id="matric_number"
              v-model="form.matric_number"
              type="text"
              required
              placeholder="e.g. RUN/2019/001"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
            />
            <p v-if="errors.matric_number" class="mt-1 text-xs text-red-600">{{ errors.matric_number[0] }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
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
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="6"
                placeholder="Minimum 6 characters"
                class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
                <EyeIcon v-else class="w-5 h-5" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password[0] }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                placeholder="Re-enter password"
                class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none transition"
              />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                <EyeSlashIcon v-if="showConfirmPassword" class="w-5 h-5" />
                <EyeIcon v-else class="w-5 h-5" />
              </button>
            </div>
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
            {{ loading ? 'Creating Account...' : 'Register' }}
          </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-600">
      Already have an account?
      <router-link :to="{ name: 'applicant-login' }" class="text-run-blue font-medium hover:underline">Login here</router-link>
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
const showConfirmPassword = ref(false);

const form = ref({
  surname: '',
  first_name: '',
  other_name: '',
  matric_number: '',
  email: '',
  phone: '',
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
    await authStore.register(form.value);
    router.push({ name: 'applicant-login', query: { registered: '1' } });
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
