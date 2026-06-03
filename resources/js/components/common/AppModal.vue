<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel :class="['w-full bg-white rounded-xl shadow-xl transform transition-all', sizeClasses[size]]">
              <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <DialogTitle class="text-sm font-semibold text-gray-900">
                  {{ title }}
                </DialogTitle>
                <button
                  type="button"
                  class="text-gray-400 hover:text-gray-600 transition-colors"
                  @click="$emit('close')"
                >
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </div>

              <div class="px-4 py-3">
                <slot />
              </div>

              <div v-if="$slots.footer" class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                <slot name="footer" />
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
  },
});

defineEmits(['close']);

const sizeClasses = {
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-lg',
  xl: 'max-w-xl',
};
</script>
