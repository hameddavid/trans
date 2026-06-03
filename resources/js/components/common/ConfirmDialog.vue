<template>
  <AppModal :show="show" :title="title" size="sm" @close="$emit('cancel')">
    <div class="text-center sm:text-left">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" :class="iconBgClass">
        <ExclamationTriangleIcon v-if="variant === 'danger' || variant === 'warning'" class="h-6 w-6" :class="iconClass" />
        <InformationCircleIcon v-else class="h-6 w-6" :class="iconClass" />
      </div>
      <p class="mt-4 text-sm text-gray-600">{{ message }}</p>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton variant="secondary" @click="$emit('cancel')">
          {{ cancelText }}
        </AppButton>
        <AppButton :variant="variant === 'info' ? 'primary' : variant" @click="$emit('confirm')">
          {{ confirmText }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from 'vue';
import { ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import AppModal from './AppModal.vue';
import AppButton from './AppButton.vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: '',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  variant: {
    type: String,
    default: 'warning',
    validator: (v) => ['danger', 'warning', 'info'].includes(v),
  },
});

defineEmits(['confirm', 'cancel']);

const iconBgClass = computed(() => ({
  danger: 'bg-red-100',
  warning: 'bg-yellow-100',
  info: 'bg-blue-100',
})[props.variant]);

const iconClass = computed(() => ({
  danger: 'text-red-600',
  warning: 'text-yellow-600',
  info: 'text-blue-600',
})[props.variant]);
</script>
