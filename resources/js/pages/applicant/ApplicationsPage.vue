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
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.matric_number }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ app.transcript_type }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.destination || '-' }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <span :class="statusBadgeClass(app.app_status)">{{ app.app_status }}</span>
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
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700">{{ app.matric_number }}</td>
                  <td class="px-4 py-2 whitespace-nowrap text-gray-700 capitalize">{{ app.transcript_type }}</td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <span :class="statusBadgeClass(app.app_status)">{{ app.app_status }}</span>
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
            <span class="font-medium text-gray-900">{{ selectedApp.matric_number }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Type</span>
            <span class="font-medium text-gray-900 capitalize">{{ selectedApp.transcript_type }}</span>
          </div>
          <div v-if="selectedApp.destination" class="flex justify-between">
            <span class="text-gray-500">Destination</span>
            <span class="font-medium text-gray-900">{{ selectedApp.destination }}</span>
          </div>
          <div v-if="selectedApp.recipient" class="flex justify-between">
            <span class="text-gray-500">Recipient</span>
            <span class="font-medium text-gray-900">{{ selectedApp.recipient }}</span>
          </div>
          <div v-if="selectedApp.delivery_mode" class="flex justify-between">
            <span class="text-gray-500">Delivery Mode</span>
            <span class="font-medium text-gray-900 capitalize">{{ selectedApp.delivery_mode }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span :class="statusBadgeClass(selectedApp.app_status)">{{ selectedApp.app_status }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Date Applied</span>
            <span class="font-medium text-gray-900">{{ formatDate(selectedApp.created_at) }}</span>
          </div>

          <!-- Courier Submission Section -->
          <template v-if="selectedApp.courier_status">
            <div class="border-t border-gray-200 pt-3 mt-3">
              <h4 class="font-semibold text-gray-900 mb-2">Courier / Shipping</h4>

              <!-- Status: pending — show form -->
              <div v-if="selectedApp.courier_status === 'pending'" class="space-y-3">
                <p class="text-xs text-gray-500">Your transcript is ready for dispatch. Please provide your courier details and upload proof of payment.</p>

                <div v-if="selectedApp.courier_notes" class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                  <strong>Note from admin:</strong> {{ selectedApp.courier_notes }}
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Courier Company *</label>
                  <input v-model="courierForm.courier_company" type="text" placeholder="e.g. DHL, FedEx, GIG Logistics"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Contact Details *</label>
                  <input v-model="courierForm.courier_contact" type="text" placeholder="Phone number or email of courier agent"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Tracking Number (if available)</label>
                  <input v-model="courierForm.courier_tracking" type="text" placeholder="Optional"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-blue focus:border-run-blue outline-none" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Payment Receipt / Evidence *</label>
                  <input type="file" ref="courierReceiptInput" accept=".jpg,.jpeg,.png,.pdf" @change="handleCourierFile"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-run-blue/10 file:text-run-blue hover:file:bg-run-blue/20" />
                  <p class="text-[11px] text-gray-400 mt-1">JPG, PNG, or PDF. Max 5MB.</p>
                </div>
                <button
                  @click="handleSubmitCourier"
                  :disabled="courierSubmitting"
                  class="w-full bg-run-blue text-white py-2.5 rounded-lg text-sm font-medium hover:bg-run-blue/90 transition disabled:opacity-50"
                >
                  {{ courierSubmitting ? 'Submitting...' : 'Submit Courier Details' }}
                </button>
              </div>

              <!-- Status: submitted — show what was submitted -->
              <div v-else-if="selectedApp.courier_status === 'submitted'" class="space-y-1.5">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700 mb-2">
                  Your courier details have been submitted and are awaiting verification.
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Company</span><span class="font-medium text-gray-900">{{ selectedApp.courier_company }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="font-medium text-gray-900">{{ selectedApp.courier_contact }}</span></div>
                <div v-if="selectedApp.courier_tracking" class="flex justify-between"><span class="text-gray-500">Tracking #</span><span class="font-medium text-gray-900">{{ selectedApp.courier_tracking }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Submitted</span><span class="font-medium text-gray-900">{{ formatDate(selectedApp.courier_submitted_at) }}</span></div>
              </div>

              <!-- Status: verified — confirmed -->
              <div v-else-if="selectedApp.courier_status === 'verified'" class="space-y-1.5">
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-xs text-green-700 mb-2">
                  Your courier details have been verified. Your transcript will be dispatched shortly.
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Company</span><span class="font-medium text-gray-900">{{ selectedApp.courier_company }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="font-medium text-gray-900">{{ selectedApp.courier_contact }}</span></div>
                <div v-if="selectedApp.courier_tracking" class="flex justify-between"><span class="text-gray-500">Tracking #</span><span class="font-medium text-gray-900">{{ selectedApp.courier_tracking }}</span></div>
              </div>
            </div>
          </template>
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
import { useToast } from 'vue-toastification';
import { useApplicationStore } from '@/stores/application';
import * as applicantApi from '@/api/applicantApi';

const toast = useToast();
const applicationStore = useApplicationStore();

const activeTab = ref('official');
const selectedApp = ref(null);

const courierForm = ref({ courier_company: '', courier_contact: '', courier_tracking: '' });
const courierFile = ref(null);
const courierReceiptInput = ref(null);
const courierSubmitting = ref(false);

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
  courierForm.value = { courier_company: '', courier_contact: '', courier_tracking: '' };
  courierFile.value = null;
}

function handleCourierFile(e) {
  courierFile.value = e.target.files[0] || null;
}

async function handleSubmitCourier() {
  if (!courierForm.value.courier_company || !courierForm.value.courier_contact) {
    toast.error('Please fill in the courier company and contact details.');
    return;
  }
  if (!courierFile.value) {
    toast.error('Please upload proof of payment.');
    return;
  }

  courierSubmitting.value = true;
  try {
    const formData = new FormData();
    formData.append('application_id', selectedApp.value.id);
    formData.append('courier_company', courierForm.value.courier_company);
    formData.append('courier_contact', courierForm.value.courier_contact);
    formData.append('courier_tracking', courierForm.value.courier_tracking || '');
    formData.append('courier_receipt', courierFile.value);

    await applicantApi.submitCourierDetails(formData);
    toast.success('Courier details submitted successfully.');
    selectedApp.value = null;
    applicationStore.fetchOfficialApplications();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to submit courier details.');
  } finally {
    courierSubmitting.value = false;
  }
}

onMounted(() => {
  applicationStore.fetchOfficialApplications();
  applicationStore.fetchStudentApplications();
});
</script>
