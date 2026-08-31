<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-bold text-gray-900">Admin Users</h1>
      <div class="flex items-center gap-2">
        <button
          @click="handleResetAll"
          :disabled="resetting"
          class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition disabled:opacity-50"
        >
          {{ resetting ? 'Resetting...' : 'Activate All' }}
        </button>
        <button
          @click="showForm = !showForm"
          class="bg-run-dark text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition"
        >
          {{ showForm ? 'Cancel' : 'Add Admin' }}
        </button>
      </div>
    </div>

    <!-- Add Admin Form -->
    <div v-if="showForm" class="bg-white rounded-lg shadow p-5 mb-5">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Register New Admin</h2>
      <p class="text-xs text-gray-500 mb-4">Enter the staff email address. Their name and details will be pulled from the staff portal.</p>

      <form @submit.prevent="handleAdd" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="staff@run.edu.ng"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
        </div>
        <div class="w-full sm:w-44">
          <select
            v-model="form.role"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none bg-white"
          >
            <option value="" disabled>Select role</option>
            <option value="200">Recommender</option>
            <option value="300">Approver</option>
            <option value="400">Super Admin</option>
          </select>
        </div>
        <button
          type="submit"
          :disabled="submitting"
          class="bg-run-dark text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50 whitespace-nowrap"
        >
          {{ submitting ? 'Adding...' : 'Add User' }}
        </button>
      </form>
      <p v-if="formError" class="text-xs text-red-600 mt-2">{{ formError }}</p>
      <p v-if="formSuccess" class="text-xs text-green-600 mt-2">{{ formSuccess }}</p>
    </div>

    <!-- Pending Access Requests -->
    <div v-if="accessRequests.length > 0" class="bg-amber-50 border border-amber-200 rounded-lg shadow mb-5">
      <div class="px-4 py-3 border-b border-amber-200">
        <h2 class="text-sm font-semibold text-amber-800">
          Pending Access Requests
          <span class="ml-1 bg-amber-200 text-amber-900 text-[10px] px-1.5 py-0.5 rounded-full font-bold">{{ accessRequests.length }}</span>
        </h2>
        <p class="text-xs text-amber-600 mt-0.5">Staff members who tried to log in but don't have access yet.</p>
      </div>
      <div class="divide-y divide-amber-100">
        <div v-for="req in accessRequests" :key="req.id" class="p-4">
          <div class="flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900">{{ req.staff_name }}</p>
              <p class="text-xs text-gray-600">{{ req.email }}</p>
              <p v-if="req.department || req.title" class="text-xs text-gray-400 mt-0.5">
                <span v-if="req.title">{{ req.title }}</span>
                <span v-if="req.title && req.department"> &middot; </span>
                <span v-if="req.department">{{ req.department }}</span>
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <select
                v-model="req._role"
                class="text-xs border border-gray-200 rounded-md px-1.5 py-1.5 bg-white focus:ring-1 focus:ring-run-dark outline-none"
              >
                <option value="200">Recommender</option>
                <option value="300">Approver</option>
                <option value="400">Super Admin</option>
              </select>
              <button
                @click="handleApproveRequest(req)"
                :disabled="requestLoading === req.id"
                class="text-xs bg-green-50 text-green-700 hover:bg-green-100 px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
              >
                Grant Access
              </button>
              <button
                @click="handleRejectRequest(req)"
                :disabled="requestLoading === req.id"
                class="text-xs bg-red-50 text-red-700 hover:bg-red-100 px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
              >
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-lg shadow p-3 mb-4 flex flex-col sm:flex-row gap-3">
      <div class="flex-1">
        <input
          v-model="search"
          @input="debouncedFetch"
          type="text"
          placeholder="Search by name or email..."
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
        />
      </div>
      <div class="flex gap-2">
        <select
          v-model="filterStatus"
          @change="fetchUsers"
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none bg-white"
        >
          <option value="">All Status</option>
          <option value="ACTIVE">Active</option>
          <option value="INACTIVE">Inactive</option>
          <option value="NONE">Not Set</option>
        </select>
        <select
          v-model="filterRole"
          @change="fetchUsers"
          class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none bg-white"
        >
          <option value="">All Roles</option>
          <option value="200">Recommender</option>
          <option value="300">Approver</option>
          <option value="400">Super Admin</option>
        </select>
      </div>
    </div>

    <!-- Bulk Action Bar -->
    <div
      v-if="selectedIds.length > 0"
      class="bg-run-dark text-white rounded-lg shadow p-3 mb-4 flex items-center justify-between gap-3"
    >
      <span class="text-sm font-medium">{{ selectedIds.length }} selected</span>
      <div class="flex items-center gap-2 flex-wrap">
        <button
          @click="handleBulk('activate')"
          :disabled="bulkLoading"
          class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
        >
          Activate
        </button>
        <button
          @click="handleBulk('deactivate')"
          :disabled="bulkLoading"
          class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
        >
          Deactivate
        </button>
        <div class="flex items-center gap-1">
          <select
            v-model="bulkRole"
            class="text-xs border border-white/30 rounded-md px-1.5 py-1.5 bg-white/10 text-white outline-none"
          >
            <option value="200" class="text-gray-900">Recommender</option>
            <option value="300" class="text-gray-900">Approver</option>
            <option value="400" class="text-gray-900">Super Admin</option>
          </select>
          <button
            @click="handleBulk('change_role')"
            :disabled="bulkLoading"
            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
          >
            Set Role
          </button>
        </div>
        <button
          @click="handleBulk('delete')"
          :disabled="bulkLoading"
          class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md font-medium transition disabled:opacity-50"
        >
          Delete
        </button>
        <button
          @click="selectedIds = []"
          class="text-xs text-white/70 hover:text-white px-2 py-1.5 transition"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-lg shadow">
      <div v-if="loading" class="p-8 text-center text-gray-500">Loading admin users...</div>

      <div v-else-if="users.length === 0" class="p-8 text-center text-gray-500">No admin users found.</div>

      <template v-else>
        <!-- Select All Header -->
        <div class="px-4 py-2 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
          <input
            type="checkbox"
            :checked="allSelected"
            :indeterminate="someSelected && !allSelected"
            @change="toggleSelectAll"
            class="rounded border-gray-300 text-run-dark focus:ring-run-dark"
          />
          <span class="text-xs text-gray-500">
            {{ allSelected ? 'Deselect all' : 'Select all' }}
            <span class="text-gray-400">({{ selectableUsers.length }} users)</span>
          </span>
        </div>

        <div class="divide-y divide-gray-100">
          <div
            v-for="u in users"
            :key="u.id"
            :class="['p-4 transition', selectedIds.includes(u.id) ? 'bg-blue-50/50' : 'hover:bg-gray-50']"
          >
            <div class="flex items-center justify-between gap-4">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <input
                  v-if="u.id !== currentUserId"
                  type="checkbox"
                  :value="u.id"
                  v-model="selectedIds"
                  class="rounded border-gray-300 text-run-dark focus:ring-run-dark shrink-0"
                />
                <div v-else class="w-4 shrink-0" />

                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-sm text-gray-900">
                      {{ u.title }} {{ u.surname }} {{ u.firstname }}
                      <span v-if="!u.surname && !u.firstname" class="italic text-gray-400">(awaiting first login)</span>
                    </span>
                    <span :class="statusClass(u.account_status)">{{ u.account_status }}</span>
                    <span :class="roleClass(u.role)">{{ roleLabel(u.role) }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">{{ u.email }}</p>
                </div>
              </div>

              <div v-if="u.id !== currentUserId" class="flex items-center gap-1.5 shrink-0">
                <button
                  @click="handleToggle(u)"
                  :disabled="actionLoading === u.id"
                  :class="u.account_status === 'ACTIVE'
                    ? 'bg-red-50 text-red-700 hover:bg-red-100'
                    : 'bg-green-50 text-green-700 hover:bg-green-100'"
                  class="text-xs px-2.5 py-1.5 rounded-md font-medium transition disabled:opacity-50"
                >
                  {{ u.account_status === 'ACTIVE' ? 'Deactivate' : 'Activate' }}
                </button>

                <select
                  :value="u.role"
                  @change="handleRoleChange(u, $event.target.value)"
                  :disabled="actionLoading === u.id"
                  class="text-xs border border-gray-200 rounded-md px-1.5 py-1.5 bg-white focus:ring-1 focus:ring-run-dark outline-none disabled:opacity-50"
                >
                  <option value="200">Recommender</option>
                  <option value="300">Approver</option>
                  <option value="400">Super Admin</option>
                </select>

                <button
                  @click="handleDelete(u)"
                  :disabled="actionLoading === u.id"
                  class="text-xs text-gray-400 hover:text-red-600 px-2 py-1.5 rounded-md transition disabled:opacity-50"
                  title="Remove"
                >
                  &times;
                </button>
              </div>

              <span v-else class="text-[10px] text-gray-400 italic shrink-0">You</span>
            </div>
          </div>
        </div>
      </template>
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

const users = ref([]);
const accessRequests = ref([]);
const loading = ref(true);
const showForm = ref(false);
const submitting = ref(false);
const resetting = ref(false);
const actionLoading = ref(null);
const requestLoading = ref(null);
const bulkLoading = ref(false);
const formError = ref('');
const formSuccess = ref('');

const search = ref('');
const filterStatus = ref('');
const filterRole = ref('');
const form = ref({ email: '', role: '' });

const selectedIds = ref([]);
const bulkRole = ref('200');

const selectableUsers = computed(() => users.value.filter(u => u.id !== currentUserId.value));
const allSelected = computed(() => selectableUsers.value.length > 0 && selectableUsers.value.every(u => selectedIds.value.includes(u.id)));
const someSelected = computed(() => selectableUsers.value.some(u => selectedIds.value.includes(u.id)));

function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = selectableUsers.value.map(u => u.id);
  }
}

let debounceTimer = null;
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchUsers, 300);
}

function statusClass(status) {
  const base = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium';
  if (status === 'ACTIVE') return `${base} bg-green-100 text-green-800`;
  if (status === 'INACTIVE') return `${base} bg-red-100 text-red-800`;
  return `${base} bg-gray-100 text-gray-500`;
}

function roleClass(role) {
  const base = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium';
  const map = {
    '200': `${base} bg-blue-50 text-blue-700`,
    '300': `${base} bg-purple-50 text-purple-700`,
    '400': `${base} bg-amber-50 text-amber-700`,
  };
  return map[role] || `${base} bg-gray-50 text-gray-600`;
}

function roleLabel(role) {
  const map = { '200': 'Recommender', '300': 'Approver', '400': 'Super Admin' };
  return map[role] || role;
}

async function fetchUsers() {
  loading.value = true;
  try {
    const params = {};
    if (search.value) params.search = search.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (filterRole.value) params.role = filterRole.value;
    const { data } = await adminApi.getAdminUsers(params);
    users.value = data.data || [];
    selectedIds.value = selectedIds.value.filter(id => users.value.some(u => u.id === id));
  } catch (e) {
    toast.error('Failed to load admin users.');
  } finally {
    loading.value = false;
  }
}

async function fetchAccessRequests() {
  try {
    const { data } = await adminApi.getAccessRequests();
    accessRequests.value = (data.data || []).map(r => ({ ...r, _role: '200' }));
  } catch (e) {
    // silent
  }
}

async function handleResetAll() {
  if (!confirm('Activate all admin accounts?')) return;
  resetting.value = true;
  try {
    const { data } = await adminApi.resetAllAdminStatus();
    toast.success(data.message);
    await fetchUsers();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to reset.');
  } finally {
    resetting.value = false;
  }
}

async function handleAdd() {
  formError.value = '';
  formSuccess.value = '';
  submitting.value = true;
  try {
    const { data } = await adminApi.createAdminUser(form.value);
    formSuccess.value = data.message || 'Admin added!';
    form.value = { email: '', role: '' };
    await fetchUsers();
  } catch (e) {
    formError.value = e.response?.data?.message
      || Object.values(e.response?.data?.errors || {}).flat().join(' ')
      || 'Failed to add admin.';
  } finally {
    submitting.value = false;
  }
}

async function handleApproveRequest(req) {
  requestLoading.value = req.id;
  try {
    const { data } = await adminApi.approveAccessRequest(req.id, { role: req._role });
    toast.success(data.message);
    await Promise.all([fetchAccessRequests(), fetchUsers()]);
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to grant access.');
  } finally {
    requestLoading.value = null;
  }
}

async function handleRejectRequest(req) {
  requestLoading.value = req.id;
  try {
    await adminApi.rejectAccessRequest(req.id);
    toast.info('Access request rejected.');
    await fetchAccessRequests();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to reject.');
  } finally {
    requestLoading.value = null;
  }
}

async function handleBulk(action) {
  const labels = { activate: 'activate', deactivate: 'deactivate', delete: 'delete', change_role: 'change role of' };
  if (!confirm(`${labels[action]} ${selectedIds.value.length} user(s)?`)) return;

  bulkLoading.value = true;
  try {
    const payload = { ids: selectedIds.value, action };
    if (action === 'change_role') payload.role = bulkRole.value;
    const { data } = await adminApi.bulkAdminAction(payload);
    toast.success(data.message);
    selectedIds.value = [];
    await fetchUsers();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Bulk action failed.');
  } finally {
    bulkLoading.value = false;
  }
}

async function handleToggle(u) {
  actionLoading.value = u.id;
  try {
    const { data } = await adminApi.toggleAdminStatus(u.id);
    toast.success(data.message);
    await fetchUsers();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Action failed.');
  } finally {
    actionLoading.value = null;
  }
}

async function handleRoleChange(u, newRole) {
  if (newRole === u.role) return;
  actionLoading.value = u.id;
  try {
    await adminApi.updateAdminRole(u.id, { role: newRole });
    toast.success(`Role updated to ${roleLabel(newRole)}.`);
    await fetchUsers();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update role.');
  } finally {
    actionLoading.value = null;
  }
}

async function handleDelete(u) {
  if (!confirm(`Remove admin "${u.email}"? This will revoke their access.`)) return;
  actionLoading.value = u.id;
  try {
    await adminApi.deleteAdminUser(u.id);
    toast.success('Admin user removed.');
    await fetchUsers();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to delete.');
  } finally {
    actionLoading.value = null;
  }
}

onMounted(() => {
  fetchUsers();
  fetchAccessRequests();
});
</script>
