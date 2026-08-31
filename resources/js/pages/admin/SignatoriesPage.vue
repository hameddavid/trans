<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-bold text-gray-900">Document Signatories</h1>
      <button
        @click="showForm = !showForm"
        class="bg-run-dark text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition"
      >
        {{ showForm ? 'Cancel' : 'Request Signatory' }}
      </button>
    </div>

    <!-- Request Form -->
    <div v-if="showForm" class="bg-white rounded-lg shadow p-5 mb-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">New Signatory Request</h2>
      <p class="text-xs text-gray-500 mb-4">Your signature will be pulled from the staff portal automatically.</p>

      <form @submit.prevent="handleSubmit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Full Name (as it appears on documents)</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="e.g. D. K. T. Akintola"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Title / Designation</label>
          <input
            v-model="form.title"
            type="text"
            required
            placeholder="e.g. Deputy Registrar, Academic Affairs Division"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Signing For</label>
          <input
            v-model="form.for_title"
            type="text"
            placeholder="e.g. REGISTRAR"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Document Type</label>
          <select
            v-model="form.document_type"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none bg-white"
          >
            <option value="" disabled>Select document type</option>
            <option value="cover_letter">Cover Letter</option>
            <option value="transcript">Transcript</option>
            <option value="proficiency">Proficiency Letter</option>
          </select>
        </div>

        <div class="sm:col-span-2 flex items-center gap-3">
          <button
            type="submit"
            :disabled="submitting"
            class="bg-run-dark text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50"
          >
            {{ submitting ? 'Submitting...' : 'Submit Request' }}
          </button>
          <p v-if="formError" class="text-xs text-red-600">{{ formError }}</p>
          <p v-if="formSuccess" class="text-xs text-green-600">{{ formSuccess }}</p>
        </div>
      </form>
    </div>

    <!-- Signatories List -->
    <div class="bg-white rounded-lg shadow">
      <div v-if="loading" class="p-8 text-center text-gray-500">Loading signatories...</div>

      <div v-else-if="signatories.length === 0" class="p-8 text-center">
        <p class="text-gray-500 mb-2">No signatories set up yet.</p>
        <p class="text-xs text-gray-400">Click "Request Signatory" to register yourself as a document signatory.</p>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div
          v-for="sig in signatories"
          :key="sig.id"
          class="p-4 hover:bg-gray-50 transition"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold text-sm text-gray-900">{{ sig.name }}</span>
                <span :class="statusBadge(sig.status)">{{ sig.status }}</span>
                <span :class="docTypeBadge(sig.document_type)">{{ formatDocType(sig.document_type) }}</span>
                <span v-if="sig.is_active" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                  ACTIVE
                </span>
              </div>
              <p class="text-xs text-gray-600 mt-0.5">{{ sig.title }}</p>
              <p class="text-xs text-gray-400 mt-0.5">For: {{ sig.for_title }} &middot; {{ sig.staff_email }}</p>
              <p v-if="sig.admin" class="text-[11px] text-gray-400 mt-1">
                Requested by {{ sig.admin.surname }} {{ sig.admin.firstname }}
                <template v-if="sig.approver">
                  &middot; {{ sig.status === 'approved' ? 'Approved' : 'Reviewed' }} by {{ sig.approver.surname }} {{ sig.approver.firstname }}
                </template>
              </p>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
              <!-- Approve/Reject for pending requests (only if not own request) -->
              <template v-if="sig.status === 'pending' && sig.admin_id !== currentUserId">
                <button
                  @click="handleApprove(sig)"
                  :disabled="actionLoading === sig.id"
                  class="text-xs bg-green-50 text-green-700 hover:bg-green-100 px-2.5 py-1.5 rounded-md font-medium transition disabled:opacity-50"
                >
                  Approve
                </button>
                <button
                  @click="handleReject(sig)"
                  :disabled="actionLoading === sig.id"
                  class="text-xs bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-md font-medium transition disabled:opacity-50"
                >
                  Reject
                </button>
              </template>

              <!-- Refresh signature for approved -->
              <button
                v-if="sig.status === 'approved'"
                @click="handleRefresh(sig)"
                :disabled="actionLoading === sig.id"
                class="text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 px-2.5 py-1.5 rounded-md font-medium transition disabled:opacity-50"
                title="Re-download signature from staff portal"
              >
                Refresh Sig.
              </button>

              <!-- Delete -->
              <button
                @click="handleDelete(sig)"
                :disabled="actionLoading === sig.id"
                class="text-xs text-gray-400 hover:text-red-600 px-2 py-1.5 rounded-md transition disabled:opacity-50"
                title="Remove"
              >
                &times;
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useAdminAuthStore } from '@/stores/adminAuth';
import * as adminApi from '@/api/adminApi';

const toast = useToast();
const adminAuthStore = useAdminAuthStore();
const currentUserId = computed(() => adminAuthStore.user?.id);

const signatories = ref([]);
const loading = ref(true);
const showForm = ref(false);
const submitting = ref(false);
const actionLoading = ref(null);
const formError = ref('');
const formSuccess = ref('');

const form = ref({
  name: '',
  title: '',
  for_title: 'REGISTRAR',
  document_type: '',
});

function statusBadge(status) {
  const base = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium';
  const map = {
    pending: `${base} bg-yellow-100 text-yellow-800`,
    approved: `${base} bg-green-100 text-green-800`,
    rejected: `${base} bg-red-100 text-red-800`,
  };
  return map[status] || `${base} bg-gray-100 text-gray-800`;
}

function docTypeBadge(type) {
  const base = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium';
  const map = {
    cover_letter: `${base} bg-blue-50 text-blue-700`,
    transcript: `${base} bg-purple-50 text-purple-700`,
    proficiency: `${base} bg-orange-50 text-orange-700`,
  };
  return map[type] || `${base} bg-gray-50 text-gray-600`;
}

function formatDocType(type) {
  const map = {
    cover_letter: 'Cover Letter',
    transcript: 'Transcript',
    proficiency: 'Proficiency Letter',
  };
  return map[type] || type;
}

async function fetchSignatories() {
  loading.value = true;
  try {
    const { data } = await adminApi.getSignatories();
    signatories.value = data.data || [];
  } catch (e) {
    toast.error('Failed to load signatories.');
  } finally {
    loading.value = false;
  }
}

async function handleSubmit() {
  formError.value = '';
  formSuccess.value = '';
  submitting.value = true;

  try {
    const { data } = await adminApi.createSignatory(form.value);
    formSuccess.value = data.message || 'Request submitted!';
    form.value = { name: '', title: '', for_title: 'REGISTRAR', document_type: '' };
    await fetchSignatories();
  } catch (e) {
    formError.value = e.response?.data?.message || 'Failed to submit request.';
  } finally {
    submitting.value = false;
  }
}

async function handleApprove(sig) {
  actionLoading.value = sig.id;
  try {
    await adminApi.approveSignatory(sig.id);
    toast.success(`${sig.name} approved as ${formatDocType(sig.document_type)} signatory.`);
    await fetchSignatories();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to approve.');
  } finally {
    actionLoading.value = null;
  }
}

async function handleReject(sig) {
  actionLoading.value = sig.id;
  try {
    await adminApi.rejectSignatory(sig.id);
    toast.info('Signatory request rejected.');
    await fetchSignatories();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to reject.');
  } finally {
    actionLoading.value = null;
  }
}

async function handleRefresh(sig) {
  actionLoading.value = sig.id;
  try {
    await adminApi.refreshSignature(sig.id);
    toast.success('Signature refreshed from staff portal.');
    await fetchSignatories();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to refresh signature.');
  } finally {
    actionLoading.value = null;
  }
}

async function handleDelete(sig) {
  if (!confirm(`Remove signatory "${sig.name}" for ${formatDocType(sig.document_type)}?`)) return;

  actionLoading.value = sig.id;
  try {
    await adminApi.deleteSignatory(sig.id);
    toast.success('Signatory removed.');
    await fetchSignatories();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to delete.');
  } finally {
    actionLoading.value = null;
  }
}

onMounted(fetchSignatories);
</script>
