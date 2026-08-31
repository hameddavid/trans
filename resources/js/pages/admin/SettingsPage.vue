<template>
  <div class="max-w-lg mx-auto">
    <h1 class="text-lg font-bold text-gray-900 mb-4">Settings</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-3">Account Information</h2>
      <dl class="space-y-3">
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Name</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.title }} {{ user?.surname }} {{ user?.firstname }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Email</dt>
          <dd class="text-sm font-medium text-gray-900">{{ user?.email }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-sm text-gray-500">Role</dt>
          <dd>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="roleBadgeClass">
              {{ roleLabel }}
            </span>
          </dd>
        </div>
      </dl>
      <p class="text-xs text-gray-400 mt-4">Password is managed via the staff portal.</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAdminAuthStore } from '@/stores/adminAuth'

const adminAuthStore = useAdminAuthStore()
const user = computed(() => adminAuthStore.user)

const roleLabel = computed(() => {
  const map = { '200': 'Recommender', '300': 'Approver', '400': 'Super Admin' }
  return map[user.value?.role] || 'Admin'
})

const roleBadgeClass = computed(() => {
  const map = {
    '200': 'bg-blue-100 text-blue-800',
    '300': 'bg-purple-100 text-purple-800',
    '400': 'bg-amber-100 text-amber-800',
  }
  return map[user.value?.role] || 'bg-gray-100 text-gray-800'
})
</script>
