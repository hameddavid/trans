<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">Payment & Delivery Settings</h1>

    <!-- Pricing Section -->
    <h2 class="text-sm font-semibold text-gray-700 mb-2">Application Pricing</h2>
    <p class="text-xs text-gray-500 mb-3">Adjust the amount applicants pay for each application type.</p>

    <div class="bg-white rounded-lg shadow mb-8">
      <div v-if="loading" class="p-8 text-center text-gray-500">Loading pricing...</div>

      <div v-else-if="items.length === 0" class="p-8 text-center text-gray-500">No payment items configured.</div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="item in items" :key="item.id" class="p-5">
          <div class="flex items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-sm text-gray-900">{{ item.label }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-500 font-mono">{{ item.slug }}</span>
                <span
                  :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="text-[10px] px-2 py-0.5 rounded-full font-medium"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p class="text-xs text-gray-400 mt-0.5">Current: &#x20A6;{{ formatAmount(item.amount) }}</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">&#x20A6;</span>
                <input
                  v-model="item._amount"
                  type="number"
                  min="0"
                  step="100"
                  class="w-32 pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
                />
              </div>
              <button
                @click="handleUpdate(item)"
                :disabled="saving === item.id || String(item._amount) === String(item.amount)"
                class="bg-run-dark text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50"
              >
                {{ saving === item.id ? 'Saving...' : 'Update' }}
              </button>
              <button
                @click="handleToggle(item)"
                :disabled="saving === item.id"
                :class="item.is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50'"
                class="text-xs px-2.5 py-2 rounded-lg font-medium transition disabled:opacity-50"
              >
                {{ item.is_active ? 'Disable' : 'Enable' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Courier Settings Section -->
    <h2 class="text-sm font-semibold text-gray-700 mb-2">Shipping Notification Settings</h2>
    <p class="text-xs text-gray-500 mb-3">When an official hard copy transcript is approved, the applicant is automatically emailed to arrange their own courier and submit the required documents.</p>

    <div class="bg-white rounded-lg shadow">
      <div v-if="courierLoading" class="p-8 text-center text-gray-500">Loading settings...</div>

      <form v-else @submit.prevent="handleSaveCourier" class="p-5 space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Forwarding Email</label>
          <input
            v-model="courier.courier_receipt_email"
            type="email"
            placeholder="e.g. transcript@run.edu.ng"
            class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
          <p class="text-[11px] text-gray-400 mt-1">Applicants will be told to send courier details and payment evidence to this email.</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Required Documents / Instructions</label>
          <textarea
            v-model="courier.courier_instructions"
            rows="4"
            placeholder="e.g.&#10;1. Courier company name and contact details&#10;2. Tracking number&#10;3. Evidence of payment (receipt/screenshot)&#10;4. Your matric number and application ID"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-run-dark focus:border-run-dark outline-none"
          />
          <p class="text-[11px] text-gray-400 mt-1">This is included in the email sent to the applicant. Use line breaks to list items.</p>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="courierSaving"
            class="bg-run-dark text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50"
          >
            {{ courierSaving ? 'Saving...' : 'Save Settings' }}
          </button>
          <p v-if="courierSuccess" class="text-xs text-green-600">{{ courierSuccess }}</p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import * as adminApi from '@/api/adminApi';

const toast = useToast();

const items = ref([]);
const loading = ref(true);
const saving = ref(null);

const courier = ref({
  courier_receipt_email: '',
  courier_instructions: '',
});
const courierLoading = ref(true);
const courierSaving = ref(false);
const courierSuccess = ref('');

function formatAmount(amount) {
  return Number(amount).toLocaleString('en-NG', { minimumFractionDigits: 2 });
}

async function fetchItems() {
  loading.value = true;
  try {
    const { data } = await adminApi.getPaymentItems();
    items.value = (data.data || []).map(item => ({ ...item, _amount: item.amount }));
  } catch (e) {
    toast.error('Failed to load pricing.');
  } finally {
    loading.value = false;
  }
}

async function fetchCourierSettings() {
  courierLoading.value = true;
  try {
    const { data } = await adminApi.getAppSettings('courier');
    const settings = data.data || [];
    settings.forEach(s => {
      if (courier.value.hasOwnProperty(s.key)) {
        courier.value[s.key] = s.value || '';
      }
    });
  } catch (e) {
    // silent — fields stay empty
  } finally {
    courierLoading.value = false;
  }
}

async function handleUpdate(item) {
  saving.value = item.id;
  try {
    await adminApi.updatePaymentItem(item.id, { amount: item._amount });
    toast.success(`${item.label} updated to ₦${formatAmount(item._amount)}`);
    await fetchItems();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update.');
  } finally {
    saving.value = null;
  }
}

async function handleToggle(item) {
  saving.value = item.id;
  try {
    await adminApi.updatePaymentItem(item.id, { is_active: !item.is_active });
    toast.success(`${item.label} ${item.is_active ? 'disabled' : 'enabled'}.`);
    await fetchItems();
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update.');
  } finally {
    saving.value = null;
  }
}

async function handleSaveCourier() {
  courierSaving.value = true;
  courierSuccess.value = '';
  try {
    const settings = Object.entries(courier.value).map(([key, value]) => ({ key, value: value || '' }));
    await adminApi.updateAppSettings(settings);
    courierSuccess.value = 'Courier settings saved.';
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to save.');
  } finally {
    courierSaving.value = false;
  }
}

onMounted(() => {
  fetchItems();
  fetchCourierSettings();
});
</script>
