<template>
  <div>
    <h3 v-if="title" class="text-base font-semibold text-gray-900 mb-4">{{ title }}</h3>
    <apexchart
      type="donut"
      :height="height"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  series: {
    type: Array,
    required: true,
  },
  labels: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
  height: {
    type: Number,
    default: 350,
  },
});

const chartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit',
  },
  colors: ['#282870', '#E0D35E', '#3b3ba0', '#ef4444', '#8b5cf6'],
  labels: props.labels,
  legend: {
    position: 'bottom',
    fontSize: '13px',
    labels: { colors: '#374151' },
  },
  dataLabels: {
    enabled: true,
    style: { fontSize: '12px' },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '55%',
      },
    },
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        chart: { height: 300 },
        legend: { position: 'bottom' },
      },
    },
  ],
}));
</script>
