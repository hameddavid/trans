<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">My Applications</h1>
      <router-link
        :to="{ name: 'applicant-apply' }"
        class="bg-run-blue text-white px-5 py-2.5 rounded-lg font-medium text-sm hover:bg-run-blue/90 transition"
      >
        New Application
      </router-link>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'px-4 py-2 text-xs font-medium border-b-2 transition',
              activeTab === tab.key
                ? 'border-run-blue text-run-blue'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <div v-if="loading" class="p-8 text-center text-gray-500">
        Loading applications...
      </div>

      <template v-else>
        <div v-if="activeTab === 'official'">
          <div v-if="officialApplications.length === 0" class="p-8 text-center">
            <DocumentTextIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500 mb-4">No official transcript applications found.</p>
            <router-link
              :to="{ name: 'applicant-apply' }"
              class="text-run-blue hover:text-run-blue/80 font-medium text-sm"
            >
              Apply for a transcript &rarr;
            </router-link>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">App ID</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Matric No.</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="app in officialApplications" :key="app.id" class="hover:bg-gray-50">
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 font-mono text-xs">{{ app.id }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.matno }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ app.type }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.destination || '-' }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <span :class="statusBadgeClass(app.status)">{{ app.status }}</span>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-500">{{ formatDate(app.created_at) }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <button
                      @click="viewApplication(app)"
                      class="text-run-blue hover:text-run-blue/80 font-medium text-sm"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="activeTab === 'student'">
          <div v-if="studentApplications.length === 0" class="p-8 text-center">
            <DocumentTextIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500 mb-4">No student copy applications found.</p>
            <router-link
              :to="{ name: 'applicant-apply' }"
              class="text-run-blue hover:text-run-blue/80 font-medium text-sm"
            >
              Apply for a transcript &rarr;
            </router-link>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">App ID</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Matric No.</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="app in studentApplications" :key="app.id" class="hover:bg-gray-50">
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 font-mono text-xs">{{ app.id }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.matno }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ app.type }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <span :class="statusBadgeClass(app.status)">{{ app.status }}</span>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-500">{{ formatDate(app.created_at) }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <button
                      @click="viewApplication(app)"
                      class="text-run-blue hover:text-run-blue/80 font-medium text-sm"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <div
      v-if="selectedApp"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="selectedApp = null"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Application Details</h3>
          <button @click="selectedApp = null" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
        <div class="px-4 py-3 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Application ID</span>
            <span class="font-mono font-medium text-gray-900">{{ selectedApp.id }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Matric Number</span>
            <span class="font-medium text-gray-900">{{ selectedApp.matno }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Type</span>
            <span class="font-medium text-gray-900 capitalize">{{ selectedApp.type }}</span>
          </div>
          <div v-if="selectedApp.destination" class="flex justify-between">
            <span class="text-gray-500">Destination</span>
            <span class="font-medium text-gray-900">{{ selectedApp.destination }}</span>
          </div>
          <div v-if="selectedApp.recipient_name" class="flex justify-between">
            <span class="text-gray-500">Recipient</span>
            <span class="font-medium text-gray-900">{{ selectedApp.recipient_name }}</span>
          </div>
          <div v-if="selectedApp.delivery_mode" class="flex justify-between">
            <span class="text-gray-500">Delivery Mode</span>
            <span class="font-medium text-gray-900 capitalize">{{ selectedApp.delivery_mode }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span :class="statusBadgeClass(selectedApp.status)">{{ selectedApp.status }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Date Applied</span>
            <span class="font-medium text-gray-900">{{ formatDate(selectedApp.created_at) }}</span>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">
          <button
            @click="selectedApp = null"
            class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-200 transition"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { DocumentTextIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useApplicationStore } from '@/stores/application';

const applicationStore = useApplicationStore();

const activeTab = ref('official');
const selectedApp = ref(null);

const tabs = [
  { key: 'official', label: 'Official Applications' },
  { key: 'student', label: 'Student Applications' },
];

const loading = computed(() => applicationStore.loading);
const officialApplications = computed(() => applicationStore.officialApplications || []);
const studentApplications = computed(() => applicationStore.studentApplications || []);

function formatDate(dateStr) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' });
}

function statusBadgeClass(status) {
  const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
  const map = {
    PENDING: `${base} bg-yellow-100 text-yellow-800`,
    RECOMMENDED: `${base} bg-blue-100 text-blue-800`,
    APPROVED: `${base} bg-green-100 text-green-800`,
    FAILED: `${base} bg-red-100 text-red-800`,
  };
  return map[(status || '').toUpperCase()] || `${base} bg-gray-100 text-gray-800`;
}

function viewApplication(app) {
  selectedApp.value = app;
}

onMounted(() => {
  applicationStore.fetchOfficialApplications();
  applicationStore.fetchStudentApplications();
});
</script>
