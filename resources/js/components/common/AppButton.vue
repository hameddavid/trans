<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[baseClasses, variantClasses[variant], sizeClasses[size], (disabled || loading) && 'opacity-50 cursor-not-allowed']"
  >
    <template v-if="loading">
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
      Processing...
    </template>
    <slot v-else />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
    validator: (v) => ['button', 'submit'].includes(v),
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'danger', 'success', 'outline'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-run-blue';

const variantClasses = {
  primary: 'bg-run-blue hover:bg-run-blue/90 text-white',
  secondary: 'bg-gray-200 hover:bg-gray-300 text-gray-800',
  danger: 'bg-red-600 hover:bg-red-700 text-white',
  success: 'bg-run-gold hover:bg-run-gold/90 text-run-blue',
  outline: 'border border-run-blue text-run-blue hover:bg-run-blue/10',
};

const sizeClasses = {
  sm: 'px-2.5 py-1 text-xs',
  md: 'px-3 py-1.5 text-xs',
  lg: 'px-5 py-2 text-sm',
};
</script>
