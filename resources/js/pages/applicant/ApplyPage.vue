<template>
  <div>
    <h1 class="text-lg font-bold text-gray-900 mb-4">Apply for Transcript</h1>

    <div class="mb-8">
      <div class="flex items-center justify-center">
        <template v-for="(step, index) in steps" :key="index">
          <div class="flex items-center">
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-colors',
                currentStep > index + 1 ? 'bg-run-gold text-run-blue' :
                currentStep === index + 1 ? 'bg-run-blue text-white' :
                'bg-gray-200 text-gray-500'
              ]"
            >
              <CheckIcon v-if="currentStep > index + 1" class="w-5 h-5" />
              <span v-else>{{ index + 1 }}</span>
            </div>
            <span
              :class="[
                'ml-2 text-sm font-medium hidden sm:inline',
                currentStep === index + 1 ? 'text-run-blue' :
                currentStep > index + 1 ? 'text-run-gold' :
                'text-gray-400'
              ]"
            >
              {{ step }}
            </span>
          </div>
          <div
            v-if="index < steps.length - 1"
            :class="[
              'w-12 sm:w-20 h-0.5 mx-2',
              currentStep > index + 1 ? 'bg-run-gold' : 'bg-gray-200'
            ]"
          />
        </template>
      </div>
    </div>

    <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
      {{ error }}
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div v-if="currentStep === 1">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Check Availability</h2>

        <div class="space-y-4 max-w-lg">
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <p class="text-sm text-gray-500">Matriculation Number</p>
            <p class="text-base font-semibold text-gray-900">{{ form.matno }}</p>
          </div>

          <div>
            <label for="appType" class="block text-sm font-medium text-gray-700 mb-1">Application Type</label>
            <select
              id="appType"
              v-model="form.applicationType"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
            >
              <option value="">Select type</option>
              <option value="official">Official Transcript</option>
              <option value="student">Student's Copy</option>
            </select>
          </div>

          <button
            @click="checkAvailability"
            :disabled="checking || !form.applicationType"
            class="bg-run-blue text-white px-4 py-2 rounded-md font-medium hover:bg-run-blue/90 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ checking ? 'Checking...' : 'Check Availability' }}
          </button>
        </div>

        <div v-if="studentInfo" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="font-semibold text-blue-900 mb-2">Student Information</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-blue-800">
            <p><span class="font-medium">Name:</span> {{ studentInfo.name }}</p>
            <p><span class="font-medium">Matric Number:</span> {{ studentInfo.matric_number }}</p>
            <p><span class="font-medium">Date of Birth:</span> {{ studentInfo.date_of_birth }}</p>
            <p><span class="font-medium">Programme:</span> {{ studentInfo.programme }}</p>
            <p><span class="font-medium">Department:</span> {{ studentInfo.department }}</p>
            <p><span class="font-medium">College:</span> {{ studentInfo.college }}</p>
            <p><span class="font-medium">First Session:</span> {{ studentInfo.first_session }}</p>
            <p><span class="font-medium">Last Session:</span> {{ studentInfo.last_session }}</p>
            <p><span class="font-medium">Year Graduated:</span> {{ studentInfo.graduation_year }}</p>
            <p><span class="font-medium">Status:</span> {{ studentInfo.status }}</p>
          </div>
          <div class="mt-4 flex items-center gap-3">
            <button
              @click="proceedToStep2"
              class="bg-run-blue text-white px-4 py-1.5 rounded-md font-medium hover:bg-run-blue/90 transition"
            >
              Proceed
            </button>
            <button
              @click="showComplaintModal = true"
              class="text-red-600 text-sm font-medium hover:underline"
            >
              Report Incorrect Information
            </button>
          </div>
        </div>

        <!-- Complaint Modal -->
        <div v-if="showComplaintModal" class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="fixed inset-0 bg-black/50" @click="showComplaintModal = false" />
          <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-4 z-10">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Report Incorrect Information</h3>
            <p class="text-sm text-gray-500 mb-4">Describe what is incorrect and the admin team will review it.</p>

            <form @submit.prevent="submitComplaint" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select v-model="complaint.subject" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue">
                  <option value="">Select an issue</option>
                  <option value="Incorrect Name">Incorrect Name</option>
                  <option value="Incorrect Programme/Department">Incorrect Programme/Department</option>
                  <option value="Incorrect Matric Number">Incorrect Matric Number</option>
                  <option value="Incorrect Status">Incorrect Status</option>
                  <option value="Missing Results">Missing Results</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                <textarea
                  v-model="complaint.message"
                  required
                  rows="4"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
                  placeholder="Please describe what information is incorrect and what the correct information should be..."
                />
              </div>

              <div v-if="complaintError" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                {{ complaintError }}
              </div>

              <div class="flex justify-end gap-3">
                <button type="button" @click="showComplaintModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                  Cancel
                </button>
                <button type="submit" :disabled="submittingComplaint" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                  {{ submittingComplaint ? 'Submitting...' : 'Submit Complaint' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div v-if="currentStep === 2">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Application Details</h2>

        <div v-if="form.applicationType === 'official'" class="space-y-4 max-w-lg">
          <div>
            <label for="recipientName" class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
            <input
              id="recipientName"
              v-model="form.recipientName"
              type="text"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
              placeholder="Name of receiving institution/organization"
            />
            <p v-if="validationErrors.recipientName" class="text-red-600 text-xs mt-1">{{ validationErrors.recipientName }}</p>
          </div>

          <div>
            <label for="recipientAddress" class="block text-sm font-medium text-gray-700 mb-1">Recipient Address</label>
            <textarea
              id="recipientAddress"
              v-model="form.recipientAddress"
              rows="3"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
              placeholder="Full address of receiving institution"
            ></textarea>
            <p v-if="validationErrors.recipientAddress" class="text-red-600 text-xs mt-1">{{ validationErrors.recipientAddress }}</p>
          </div>

          <div>
            <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
            <select
              id="destination"
              v-model="form.destinationId"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
            >
              <option value="">Select destination</option>
              <option v-for="dest in destinations" :key="dest.id" :value="dest.id">
                {{ dest.name }} &mdash; &#x20A6;{{ formatAmount(dest.amount) }}
              </option>
            </select>
            <p v-if="validationErrors.destinationId" class="text-red-600 text-xs mt-1">{{ validationErrors.destinationId }}</p>
          </div>

          <div>
            <label for="deliveryMode" class="block text-sm font-medium text-gray-700 mb-1">Delivery Mode</label>
            <select
              id="deliveryMode"
              v-model="form.deliveryMode"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
            >
              <option value="">Select delivery mode</option>
              <option value="soft_copy">Soft Copy</option>
              <option value="hard_copy">Hard Copy</option>
              <option value="wes">WES</option>
              <option value="portal">Portal</option>
            </select>
            <p v-if="validationErrors.deliveryMode" class="text-red-600 text-xs mt-1">{{ validationErrors.deliveryMode }}</p>
          </div>

          <div>
            <label for="copies" class="block text-sm font-medium text-gray-700 mb-1">Number of Copies</label>
            <input
              id="copies"
              v-model.number="form.copies"
              type="number"
              min="1"
              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-run-blue focus:border-run-blue"
            />
          </div>

          <div>
            <label for="certificate" class="block text-sm font-medium text-gray-700 mb-1">Upload Certificate (optional)</label>
            <input
              id="certificate"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleFileUpload"
              class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-run-blue/10 file:text-run-blue hover:file:bg-run-blue/20"
            />
          </div>
        </div>

        <div v-else class="max-w-lg">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800">You are applying for a <strong>Student's Copy</strong> of your transcript. No additional details are required.</p>
            <p class="text-sm text-blue-600 mt-2">Matric Number: {{ form.matno }}</p>
          </div>
        </div>

        <div class="flex items-center space-x-3 mt-6">
          <button
            @click="currentStep = 1"
            class="px-4 py-2 rounded-md font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition"
          >
            Back
          </button>
          <button
            @click="proceedToStep3"
            :disabled="submitting"
            class="bg-run-blue text-white px-4 py-2 rounded-md font-medium hover:bg-run-blue/90 transition disabled:opacity-50"
          >
            {{ submitting ? 'Processing...' : 'Continue to Payment' }}
          </button>
        </div>
      </div>

      <div v-if="currentStep === 3">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Payment</h2>

        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-lg">
          <h3 class="font-medium text-gray-900 mb-2">Payment Summary</h3>
          <div class="space-y-1 text-sm text-gray-700">
            <div class="flex justify-between">
              <span>Application Type:</span>
              <span class="font-medium capitalize">{{ form.applicationType }} Transcript</span>
            </div>
            <div v-if="selectedDestination" class="flex justify-between">
              <span>Destination:</span>
              <span class="font-medium">{{ selectedDestination.name }}</span>
            </div>
            <div class="flex justify-between">
              <span>Copies:</span>
              <span class="font-medium">{{ form.copies }}</span>
            </div>
            <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
              <span class="font-semibold text-gray-900">Total Amount:</span>
              <span class="font-bold text-gray-900">&#x20A6;{{ formatAmount(paymentAmount) }}</span>
            </div>
            <div v-if="currentRRR" class="flex justify-between mt-2">
              <span>RRR:</span>
              <span class="font-mono font-medium text-run-blue">{{ currentRRR }}</span>
            </div>
          </div>
        </div>

        <div v-if="!currentRRR" class="max-w-lg space-y-4">
          <h3 class="font-medium text-gray-900">Select Payment Method</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button
              v-for="gw in paymentGateways"
              :key="gw.id"
              @click="!gw.disabled && (selectedGateway = gw.id)"
              :disabled="gw.disabled"
              :class="[
                'flex items-center gap-3 p-4 rounded-lg border-2 transition text-left',
                gw.disabled
                  ? 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed'
                  : selectedGateway === gw.id
                    ? 'border-run-blue bg-run-blue/5'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
              ]"
            >
              <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :class="gw.iconBg">
                <CreditCardIcon class="w-5 h-5" :class="gw.iconColor" />
              </div>
              <div>
                <p class="font-semibold text-sm" :class="gw.disabled ? 'text-gray-400' : 'text-gray-900'">{{ gw.name }}</p>
                <p class="text-xs" :class="gw.disabled ? 'text-gray-400' : 'text-gray-500'">{{ gw.description }}</p>
              </div>
              <div v-if="selectedGateway === gw.id && !gw.disabled" class="ml-auto">
                <CheckCircleIcon class="w-5 h-5 text-run-blue" />
              </div>
            </button>
          </div>

          <button
            @click="initiatePayment"
            :disabled="processingPayment || !selectedGateway"
            class="flex items-center justify-center gap-3 w-full p-4 rounded-lg bg-run-blue text-white font-semibold hover:bg-run-blue/90 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ processingPayment ? 'Processing...' : 'Proceed to Payment' }}
          </button>
        </div>

        <div v-else class="max-w-lg space-y-4">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
              Your RRR is <span class="font-mono font-bold text-lg">{{ currentRRR }}</span>
            </p>
            <p class="text-xs text-blue-600 mt-1">
              {{ isPendingRRR ? 'You have a pending payment for this destination. Pay or re-query below.' : 'Click the button below to proceed to Remita\'s payment page.' }}
            </p>
          </div>

          <div class="flex flex-col gap-3">
            <form ref="remitaForm" :action="paymentUrl" method="POST" target="_blank">
              <input type="hidden" name="rrr" :value="currentRRR" />
              <input type="hidden" name="merchantId" :value="remitaMerchantId" />
              <input type="hidden" name="hash" :value="remitaHash" />
              <input type="hidden" name="responseurl" :value="callbackUrl" />
              <button
                type="submit"
                class="flex items-center justify-center gap-2 w-full p-4 rounded-lg bg-run-blue text-white font-semibold hover:bg-run-blue/90 transition"
              >
                <CreditCardIcon class="w-5 h-5" />
                Proceed to Remita Payment
              </button>
            </form>

            <button
              @click="verifyAndSubmit"
              :disabled="verifyingPayment"
              class="flex items-center justify-center gap-2 w-full p-4 rounded-lg bg-run-gold text-run-blue font-semibold hover:bg-run-gold/90 transition disabled:opacity-50"
            >
              {{ verifyingPayment ? 'Verifying Payment...' : 'I Have Completed Payment — Verify & Submit' }}
            </button>

            <button
              @click="requeryOnly"
              :disabled="requerying"
              class="flex items-center justify-center gap-2 w-full p-3 rounded-lg border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition disabled:opacity-50"
            >
              {{ requerying ? 'Checking...' : 'Re-query Transaction' }}
            </button>
          </div>
        </div>

        <div class="mt-6">
          <button
            @click="currentStep = 2"
            class="px-4 py-2 rounded-md font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition"
          >
            Back
          </button>
        </div>
      </div>

      <div v-if="currentStep === 4">
        <div class="text-center py-8">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="w-10 h-10 text-run-gold" />
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Application Submitted Successfully</h2>
          <p class="text-gray-600 mb-1">Your application has been received and is being processed.</p>
          <p class="text-sm text-gray-500 mb-6">
            Application Reference: <span class="font-mono font-semibold text-run-blue">{{ applicationRef }}</span>
          </p>
          <router-link
            :to="{ name: 'applicant-applications' }"
            class="inline-block bg-run-blue text-white px-8 py-3 rounded-lg font-medium hover:bg-run-blue/90 transition"
          >
            View My Applications
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { CheckIcon, CheckCircleIcon, CreditCardIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import { useApplicationStore } from '@/stores/application';
import * as applicantApi from '@/api/applicantApi';

const toast = useToast();
const authStore = useAuthStore();
const applicationStore = useApplicationStore();

const steps = ['Check Availability', 'Application Details', 'Payment', 'Confirmation'];
const currentStep = ref(1);
const checking = ref(false);
const processingPayment = ref(false);
const verifyingPayment = ref(false);
const error = ref('');
const studentInfo = ref(null);
const applicationRef = ref('');
const currentRRR = ref('');
const paymentUrl = ref('');
const remitaMerchantId = ref('');
const remitaHash = ref('');
const isPendingRRR = ref(false);
const requerying = ref(false);
const selectedGateway = ref('remita');
const callbackUrl = computed(() => window.location.origin + '/applicant/apply?payment=callback');

const paymentGateways = [
  {
    id: 'remita',
    name: 'Remita',
    description: 'Pay via Remita',
    iconBg: 'bg-orange-100',
    iconColor: 'text-orange-600',
  },
  {
    id: 'interswitch',
    name: 'Interswitch',
    description: 'Coming soon',
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600',
    disabled: true,
  },
];
const validationErrors = reactive({});
const showComplaintModal = ref(false);
const submittingComplaint = ref(false);
const complaintError = ref('');
const complaint = reactive({ subject: '', message: '' });

const form = reactive({
  matno: authStore.user?.matric_number || '',
  applicationType: '',
  recipientName: '',
  recipientAddress: '',
  destinationId: '',
  deliveryMode: '',
  copies: 1,
  certificate: null,
});

const destinations = computed(() => applicationStore.destinations || []);

const selectedDestination = computed(() => {
  if (!form.destinationId) return null;
  return destinations.value.find(d => d.id === form.destinationId);
});

const paymentAmount = computed(() => {
  if (form.applicationType === 'student') {
    return applicationStore.studentCopyAmount || 12000;
  }
  const dest = selectedDestination.value;
  if (!dest) return 0;
  return (dest.amount || 0) * form.copies;
});

function formatAmount(amount) {
  if (!amount) return '0.00';
  return Number(amount).toLocaleString('en-NG', { minimumFractionDigits: 2 });
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file) {
    form.certificate = file;
  }
}

async function checkAvailability() {
  error.value = '';
  studentInfo.value = null;
  checking.value = true;
  try {
    const result = await applicationStore.checkAvailability();
    const d = result.data ?? result;
    studentInfo.value = d.student ?? d;
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not verify matriculation number. Please try again.';
  } finally {
    checking.value = false;
  }
}

async function submitComplaint() {
  submittingComplaint.value = true;
  complaintError.value = '';
  try {
    await applicantApi.submitComplaint({
      subject: complaint.subject,
      message: complaint.message,
    });
    showComplaintModal.value = false;
    complaint.subject = '';
    complaint.message = '';
    toast.success('Your complaint has been submitted. The admin team will review it shortly.');
  } catch (e) {
    complaintError.value = e.response?.data?.message || 'Failed to submit complaint. Please try again.';
  } finally {
    submittingComplaint.value = false;
  }
}

function proceedToStep2() {
  error.value = '';
  currentStep.value = 2;
  if (form.applicationType === 'official') {
    applicationStore.fetchDestinations();
  }
}

function validateStep2() {
  Object.keys(validationErrors).forEach(k => delete validationErrors[k]);
  if (form.applicationType !== 'official') return true;

  let valid = true;
  if (!form.recipientName.trim()) {
    validationErrors.recipientName = 'Recipient name is required';
    valid = false;
  }
  if (!form.recipientAddress.trim()) {
    validationErrors.recipientAddress = 'Recipient address is required';
    valid = false;
  }
  if (!form.destinationId) {
    validationErrors.destinationId = 'Please select a destination';
    valid = false;
  }
  if (!form.deliveryMode) {
    validationErrors.deliveryMode = 'Please select a delivery mode';
    valid = false;
  }
  return valid;
}

function proceedToStep3() {
  error.value = '';
  if (!validateStep2()) return;
  currentRRR.value = '';
  paymentUrl.value = '';
  currentStep.value = 3;
}

function getDestination() {
  if (form.applicationType === 'student') return 'SOFT';
  return form.destinationId || 'SOFT';
}

function saveFormState() {
  sessionStorage.setItem('applyFormState', JSON.stringify({
    matno: form.matno,
    applicationType: form.applicationType,
    recipientName: form.recipientName,
    recipientAddress: form.recipientAddress,
    destinationId: form.destinationId,
    deliveryMode: form.deliveryMode,
    copies: form.copies,
    rrr: currentRRR.value,
  }));
}

function restoreFormState() {
  const saved = sessionStorage.getItem('applyFormState');
  if (!saved) return false;
  try {
    const state = JSON.parse(saved);
    form.applicationType = state.applicationType || '';
    form.recipientName = state.recipientName || '';
    form.recipientAddress = state.recipientAddress || '';
    form.destinationId = state.destinationId || '';
    form.deliveryMode = state.deliveryMode || '';
    form.copies = state.copies || 1;
    if (state.rrr) currentRRR.value = state.rrr;
    return true;
  } catch { return false; }
}

function clearFormState() {
  sessionStorage.removeItem('applyFormState');
}

async function initiatePayment() {
  if (!selectedGateway.value) return;

  const gw = paymentGateways.find(g => g.id === selectedGateway.value);
  if (gw?.disabled) {
    toast.info(`${gw.name} is coming soon. Please select another payment method.`);
    return;
  }

  if (selectedGateway.value === 'remita') {
    await payWithRemita();
  }
  // Future gateways: else if (selectedGateway.value === 'interswitch') { ... }
}

async function payWithRemita() {
  error.value = '';
  processingPayment.value = true;
  try {
    const destination = getDestination();
    const amount = String(paymentAmount.value);

    const { data } = await applicantApi.initiatePayment({ destination, amount });
    const result = data.data ?? data;

    currentRRR.value = result.rrr;
    paymentUrl.value = result.payment_url;
    remitaMerchantId.value = result.merchant_id;
    remitaHash.value = result.hash;
    isPendingRRR.value = !!result.is_pending;

    saveFormState();
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to generate RRR. Please try again.';
  } finally {
    processingPayment.value = false;
  }
}

async function requeryOnly() {
  requerying.value = true;
  try {
    const { data } = await applicantApi.verifyPayment({ rrr: currentRRR.value });
    if (data.status === 'success') {
      toast.success('Payment verified successfully! You can now submit your application.');
      isPendingRRR.value = false;
    } else {
      toast.warning('Payment is still pending. Please complete payment on Remita first.');
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to verify payment. Try again later.');
  } finally {
    requerying.value = false;
  }
}

async function verifyAndSubmit() {
  error.value = '';
  verifyingPayment.value = true;
  try {
    const { data: verifyResult } = await applicantApi.verifyPayment({ rrr: currentRRR.value });

    if (verifyResult.status === 'pending') {
      error.value = 'Payment has not been confirmed yet. Please complete payment on Remita first, then try again.';
      return;
    }

    const formData = new FormData();
    formData.append('type', form.applicationType);
    formData.append('rrr', currentRRR.value);

    if (form.applicationType === 'official') {
      formData.append('recipient_name', form.recipientName);
      formData.append('recipient_address', form.recipientAddress);
      formData.append('destination_id', form.destinationId);
      formData.append('delivery_mode', form.deliveryMode);
      formData.append('copies', form.copies);
    }

    if (form.certificate) {
      formData.append('certificate', form.certificate);
    }

    const result = await applicationStore.submitApplication(formData);
    applicationRef.value = result.data?.reference || result.reference || currentRRR.value;
    clearFormState();
    currentStep.value = 4;
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to verify payment. Please try again.';
  } finally {
    verifyingPayment.value = false;
  }
}

onMounted(async () => {
  applicationStore.fetchDestinations();

  const params = new URLSearchParams(window.location.search);
  const rrrFromUrl = params.get('RRR') || params.get('rrr');

  if (rrrFromUrl) {
    const restored = restoreFormState();
    if (restored) {
      currentRRR.value = rrrFromUrl;
      currentStep.value = 3;

      const status = params.get('status') || '';
      if (status.toLowerCase().includes('successful') || status.toLowerCase().includes('approved')) {
        verifyingPayment.value = true;
        try {
          await verifyAndSubmit();
        } finally {
          verifyingPayment.value = false;
        }
      }
    }

    window.history.replaceState({}, '', window.location.pathname);
  }
});
</script>
