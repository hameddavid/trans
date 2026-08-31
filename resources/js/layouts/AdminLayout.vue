<template>
  <div class="min-h-screen bg-gray-100 flex">
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-40 lg:hidden"
      @click="sidebarOpen = false"
    />

    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-run-dark text-white flex flex-col transform transition-all duration-200',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'lg:w-0 lg:overflow-hidden' : 'lg:w-64',
        'lg:translate-x-0 lg:static lg:z-auto'
      ]"
    >
      <div class="px-4 py-3 flex items-center space-x-2.5 border-b border-gray-700">
        <img src="/assets/images/run_logo.png" alt="RUN Logo" class="h-7 w-7 shrink-0" />
        <span class="font-semibold text-sm whitespace-nowrap">Admin Panel</span>
      </div>

      <nav class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">
        <SidebarLink :to="{ name: 'admin-dashboard' }" label="Dashboard" :icon="HomeIcon" @click="sidebarOpen = false" />

        <SidebarGroup label="Applications">
          <SidebarLink :to="{ name: 'admin-pending-official' }" label="Pending" :icon="DocumentTextIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-recommended-official' }" label="Recommended" :icon="DocumentTextIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-approved-official' }" label="Approved" :icon="DocumentTextIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-failed-official' }" label="Failed" :icon="DocumentTextIcon" @click="sidebarOpen = false" />
        </SidebarGroup>

        <SidebarGroup label="Student Applications">
          <SidebarLink :to="{ name: 'admin-pending-student' }" label="Pending" :icon="AcademicCapIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-recommended-student' }" label="Recommended" :icon="AcademicCapIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-approved-student' }" label="Approved" :icon="AcademicCapIcon" @click="sidebarOpen = false" />
        </SidebarGroup>

        <SidebarGroup label="Degree Verification">
          <SidebarLink :to="{ name: 'admin-pending-degree' }" label="Pending" :icon="ShieldCheckIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-recommended-degree' }" label="Recommended" :icon="ShieldCheckIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-approved-degree' }" label="Approved" :icon="ShieldCheckIcon" @click="sidebarOpen = false" />
        </SidebarGroup>

        <SidebarGroup label="Management">
          <SidebarLink :to="{ name: 'admin-applicants' }" label="Applicants" :icon="CogIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-complaints' }" label="Complaints" :icon="ExclamationTriangleIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-payments' }" label="Payments" :icon="CogIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-generated-transcripts' }" label="Generated Transcripts" :icon="CogIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-forgot-matric' }" label="Forgot Matric" :icon="CogIcon" @click="sidebarOpen = false" />
          <SidebarLink :to="{ name: 'admin-signatories' }" label="Signatories" :icon="PencilSquareIcon" @click="sidebarOpen = false" />
          <SidebarLink v-if="adminAuthStore.isSuperAdmin" :to="{ name: 'admin-users' }" label="Admin Users" :icon="UsersIcon" @click="sidebarOpen = false" />
          <SidebarLink v-if="adminAuthStore.isApprover || adminAuthStore.isSuperAdmin" :to="{ name: 'admin-pricing' }" label="Pricing" :icon="CurrencyDollarIcon" @click="sidebarOpen = false" />
        </SidebarGroup>

        <SidebarLink :to="{ name: 'admin-generate-transcript' }" label="Generate Transcript" :icon="DocumentArrowDownIcon" @click="sidebarOpen = false" />
        <SidebarLink :to="{ name: 'admin-settings' }" label="Settings" :icon="Cog6ToothIcon" @click="sidebarOpen = false" />
      </nav>

      <div class="px-2 py-2 border-t border-gray-700">
        <button
          @click="handleLogout"
          class="flex items-center space-x-2 w-full px-2.5 py-1.5 text-xs text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition"
        >
          <ArrowLeftOnRectangleIcon class="w-4 h-4" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header class="bg-white shadow-sm border-b border-gray-200 px-3 sm:px-4 lg:px-6 py-2.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700">
            <Bars3Icon class="w-5 h-5" />
          </button>
          <h1 class="text-sm font-semibold text-gray-900">{{ currentTitle }}</h1>
        </div>
        <div class="flex items-center space-x-2">
          <span class="text-xs text-gray-600 hidden sm:inline">{{ adminAuthStore.fullName || 'Admin' }}</span>
          <span class="text-[10px] bg-run-blue/10 text-run-blue px-1.5 py-0.5 rounded-full font-medium">
            {{ adminAuthStore.isSuperAdmin ? 'Super Admin' : adminAuthStore.isApprover ? 'Approver' : adminAuthStore.isRecommender ? 'Recommender' : 'Admin' }}
          </span>
          <button
            @click="handleLogout"
            class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-600 transition"
            title="Logout"
          >
            <ArrowLeftOnRectangleIcon class="w-4 h-4" />
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </header>

      <main class="flex-1 p-3 sm:p-4 lg:p-6 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAdminAuthStore } from '@/stores/adminAuth';
import {
  HomeIcon,
  DocumentTextIcon,
  AcademicCapIcon,
  ShieldCheckIcon,
  CogIcon,
  Cog6ToothIcon,
  DocumentArrowDownIcon,
  ArrowLeftOnRectangleIcon,
  ExclamationTriangleIcon,
  PencilSquareIcon,
  UsersIcon,
  CurrencyDollarIcon,
  Bars3Icon,
} from '@heroicons/vue/24/outline';
import SidebarLink from '@/components/admin/SidebarLink.vue';
import SidebarGroup from '@/components/admin/SidebarGroup.vue';

const route = useRoute();
const router = useRouter();
const adminAuthStore = useAdminAuthStore();

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

const currentTitle = computed(() => route.meta?.title || 'Dashboard');

function toggleSidebar() {
  const isLargeScreen = window.innerWidth >= 1024;
  if (isLargeScreen) {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  } else {
    sidebarOpen.value = !sidebarOpen.value;
  }
}

async function handleLogout() {
  await adminAuthStore.logout();
  router.push({ name: 'admin-login' });
}
</script>
