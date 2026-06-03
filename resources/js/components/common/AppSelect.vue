<template>
  <div>
    <label v-if="label" :for="selectId" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      :id="selectId"
      :value="modelValue"
      :required="required"
      :class="[
        'block w-full rounded-lg border shadow-sm sm:text-sm focus:ring-run-blue focus:border-run-blue',
        error ? 'border-red-500' : 'border-gray-300',
      ]"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="" disabled :selected="!modelValue">
        {{ placeholder }}
      </option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
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
  options: {
    type: Array,
    default: () => [],
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

const selectId = computed(() => props.id || `select-${Math.random().toString(36).slice(2, 9)}`);
</script>
