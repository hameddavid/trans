<template>
  <div>
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="inputId"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :class="[
        'block w-full rounded-lg border shadow-sm sm:text-sm focus:ring-run-blue focus:border-run-blue',
        error ? 'border-red-500' : 'border-gray-300',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
    validator: (v) => ['text', 'email', 'password', 'number'].includes(v),
  },
  placeholder: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);

const inputId = computed(() => props.id || `input-${Math.random().toString(36).slice(2, 9)}`);
</script>
