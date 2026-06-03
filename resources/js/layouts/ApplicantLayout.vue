<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-run-dark text-white">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
        <div class="flex items-center justify-between h-12">
          <div class="flex items-center space-x-2">
            <img src="/assets/images/run_logo.png" alt="RUN Logo" class="h-6 w-6" />
            <span class="font-semibold text-sm">Transcript Portal</span>
          </div>

          <div class="hidden md:flex items-center space-x-4">
            <router-link
              v-for="link in navLinks"
              :key="link.to"
              :to="link.to"
              class="text-xs text-gray-300 hover:text-white transition"
              active-class="text-run-gold border-b-2 border-run-gold"
            >
              {{ link.label }}
            </router-link>

            <div class="relative" ref="dropdownRef">
              <button
                @click="showDropdown = !showDropdown"
                class="flex items-center space-x-1.5 text-xs text-gray-300 hover:text-white transition"
              >
                <span>{{ authStore.user?.name || 'User' }}</span>
                <ChevronDownIcon class="w-3.5 h-3.5" />
              </button>
              <div
                v-if="showDropdown"
                class="absolute right-0 mt-1.5 w-40 bg-white rounded-md shadow-lg py-1 z-50"
              >
                <router-link
                  to="/applicant/settings"
                  class="block px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100"
                  @click="showDropdown = false"
                >
                  Settings
                </router-link>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-gray-100"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>

          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-300 hover:text-white">
            <Bars3Icon v-if="!mobileMenuOpen" class="w-5 h-5" />
            <XMarkIcon v-else class="w-5 h-5" />
          </button>
        </div>
      </div>

      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-700">
        <div class="px-3 py-2 space-y-1">
          <router-link
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="block text-xs text-gray-300 hover:text-white py-1.5"
            active-class="text-run-gold"
            @click="mobileMenuOpen = false"
          >
            {{ link.label }}
          </router-link>
          <router-link
            to="/applicant/settings"
            class="block text-xs text-gray-300 hover:text-white py-1.5"
            @click="mobileMenuOpen = false"
          >
            Settings
          </router-link>
          <button
            @click="handleLogout"
            class="block text-xs text-red-400 hover:text-red-300 py-1.5"
          >
            Logout
          </button>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-5">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Bars3Icon, XMarkIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();

const mobileMenuOpen = ref(false);
const showDropdown = ref(false);
const dropdownRef = ref(null);

const navLinks = [
  { to: '/applicant/dashboard', label: 'Dashboard' },
  { to: '/applicant/my-applications', label: 'My Applications' },
  { to: '/applicant/my-payments', label: 'My Payments' },
];

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showDropdown.value = false;
  }
}

async function handleLogout() {
  showDropdown.value = false;
  await authStore.logout();
  router.push({ name: 'applicant-login' });
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
