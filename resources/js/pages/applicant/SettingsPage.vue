<template>
  <div class="max-w-lg mx-auto">
    <h1 class="text-lg font-bold text-gray-900 mb-4">Account Settings</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-3">Change Password</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
          <input v-model="form.old_password" type="password" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.old_password" class="mt-1 text-sm text-red-500">{{ errors.old_password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
          <input v-model="form.password" type="password" required minlength="6" class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
          <p v-if="errors.password" class="mt-1 text-sm text-red-500">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
          <input v-model="form.password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 focus:ring-run-blue focus:border-run-blue" />
        </div>

        <p v-if="mismatch" class="text-sm text-red-500">Passwords do not match</p>

        <div class="pt-2">
          <button type="submit" :disabled="loading || mismatch" class="w-full flex justify-center py-2.5 px-4 rounded-lg text-white bg-run-blue hover:bg-run-blue/90 font-medium disabled:opacity-50">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ loading ? 'Updating...' : 'Update Password' }}
          </button>
        </div>

        <p v-if="successMsg" class="text-sm text-green-600 text-center">{{ successMsg }}</p>
        <p v-if="errorMsg" class="text-sm text-red-600 text-center">{{ errorMsg }}</p>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-3">Account Information</h2>
      <dl class="space-y-3">
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Name</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.surname }} {{ user?.firstname }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Email</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.email }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Matric Number</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.matric_number }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Phone</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.phone }}</dd>
        </div>
      </dl>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const loading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const errors = ref({})

const form = reactive({
  old_password: '',
  password: '',
  password_confirmation: '',
})

const mismatch = computed(() => form.password && form.password_confirmation && form.password !== form.password_confirmation)

async function handleSubmit() {
  loading.value = true
  successMsg.value = ''
  errorMsg.value = ''
  errors.value = {}

  try {
    await authStore.resetPassword(form)
    successMsg.value = 'Password updated successfully'
    form.old_password = ''
    form.password = ''
    form.password_confirmation = ''
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
</script>
