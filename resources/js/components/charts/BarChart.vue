<template>
  <div>
    <h3 v-if="title" class="text-base font-semibold text-gray-900 mb-4">{{ title }}</h3>
    <apexchart
      type="bar"
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
  categories: {
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
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'inherit',
  },
  colors: ['#282870', '#E0D35E'],
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: '55%',
    },
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: props.categories,
    labels: {
      style: { colors: '#6b7280', fontSize: '12px' },
    },
  },
  yaxis: {
    labels: {
      style: { colors: '#6b7280', fontSize: '12px' },
    },
  },
  grid: {
    borderColor: '#e5e7eb',
    strokeDashArray: 4,
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        plotOptions: {
          bar: { columnWidth: '70%' },
        },
      },
    },
  ],
}));
</script>
