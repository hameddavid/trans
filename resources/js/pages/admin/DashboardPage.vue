<template>
  <div class="space-y-4">
    <h1 class="text-lg font-bold text-gray-900">Welcome, {{ adminAuthStore.fullName }}</h1>

    <div v-if="dashboardStore.loading" class="flex justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-run-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
      </svg>
    </div>

    <template v-else>
      <!-- Student Submissions -->
      <div>
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Student Submissions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
          <!-- Official Transcript -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Official Transcript</h3>
              <DocumentTextIcon class="h-5 w-5 text-blue-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ svc('officialTranscript').total }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Pending: {{ svc('officialTranscript').pending }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                Recommended: {{ svc('officialTranscript').recommended }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Approved: {{ svc('officialTranscript').approved }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Failed: {{ svc('officialTranscript').failed }}
              </span>
            </div>
          </div>

          <!-- Student's Copy -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-teal-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Student's Copy</h3>
              <DocumentDuplicateIcon class="h-5 w-5 text-teal-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ svc('studentCopy').total }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Pending: {{ svc('studentCopy').pending }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                Recommended: {{ svc('studentCopy').recommended }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Approved: {{ svc('studentCopy').approved }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Failed: {{ svc('studentCopy').failed }}
              </span>
            </div>
          </div>

          <!-- Proficiency in English -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-amber-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Proficiency in English</h3>
              <LanguageIcon class="h-5 w-5 text-amber-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ svc('proficiency').total }}</p>
          </div>

          <!-- Degree Verification -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Degree Verification</h3>
              <AcademicCapIcon class="h-5 w-5 text-purple-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ svc('degreeVerification').total }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Pending: {{ svc('degreeVerification').pending }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                Recommended: {{ svc('degreeVerification').recommended }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Approved: {{ svc('degreeVerification').approved }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin & Revenue -->
      <div>
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Admin & Revenue</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <!-- Admin Generated -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Admin Generated</h3>
              <CogIcon class="h-5 w-5 text-gray-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ svc('adminGenerated').total }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Official: {{ svc('adminGenerated').official }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                Student's Copy: {{ svc('adminGenerated').studentCopy }}
              </span>
            </div>
          </div>

          <!-- Revenue -->
          <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Revenue</h3>
              <CurrencyDollarIcon class="h-5 w-5 text-green-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">&#8358;{{ dashboardStore.revenue.total }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Transcript: &#8358;{{ dashboardStore.revenue.transcript }}
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                Degree: &#8358;{{ dashboardStore.revenue.degree }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <h2 class="text-sm font-semibold text-gray-900 mb-3">Applications by Month</h2>
          <apexchart
            type="bar"
            height="260"
            :options="barChartOptions"
            :series="barChartSeries"
          />
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <h2 class="text-sm font-semibold text-gray-900 mb-3">Application Status Distribution</h2>
          <apexchart
            type="donut"
            height="260"
            :options="pieChartOptions"
            :series="pieChartSeries"
          />
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200">
          <h2 class="text-sm font-semibold text-gray-900">Recent Activities</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Admin</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="activity in dashboardStore.activities" :key="activity.id" class="hover:bg-gray-50">
                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ activity.date }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ activity.student }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ activity.type }}</td>
                <td class="px-4 py-2 whitespace-nowrap">
                  <span :class="statusBadgeClass(activity.action)" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium">
                    {{ activity.action }}
                  </span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">{{ activity.admin }}</td>
              </tr>
              <tr v-if="dashboardStore.activities.length === 0">
                <td colspan="5" class="px-4 py-5 text-center text-xs text-gray-500">No recent activities</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useAdminAuthStore } from '@/stores/adminAuth';
import { useDashboardStore } from '@/stores/dashboard';
import {
  DocumentTextIcon,
  DocumentDuplicateIcon,
  CurrencyDollarIcon,
  AcademicCapIcon,
  CogIcon,
  LanguageIcon,
} from '@heroicons/vue/24/outline';

const adminAuthStore = useAdminAuthStore();
const dashboardStore = useDashboardStore();

function svc(key) {
  const s = dashboardStore.services[key];
  return s ?? { total: 0, pending: 0, recommended: 0, approved: 0, failed: 0, official: 0, studentCopy: 0 };
}

function statusBadgeClass(status) {
  const map = {
    PENDING: 'bg-yellow-100 text-yellow-800',
    RECOMMENDED: 'bg-indigo-100 text-indigo-800',
    APPROVED: 'bg-green-100 text-green-800',
    FAILED: 'bg-red-100 text-red-800',
  };
  return map[status] || 'bg-gray-100 text-gray-800';
}

const barChartOptions = computed(() => ({
  chart: { id: 'applications-by-month', toolbar: { show: false }, stacked: false },
  xaxis: { categories: dashboardStore.charts.monthlyLabels },
  colors: ['#3b82f6', '#14b8a6', '#a855f7', '#6b7280'],
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
  dataLabels: { enabled: false },
  legend: { position: 'top' },
}));

const barChartSeries = computed(() => dashboardStore.charts.monthlySeries ?? []);

const pieChartOptions = computed(() => ({
  chart: { id: 'status-distribution' },
  labels: dashboardStore.charts.statusDistribution?.labels ?? [],
  colors: ['#f59e0b', '#6366f1', '#22c55e', '#ef4444'],
  legend: { position: 'bottom' },
}));

const pieChartSeries = computed(() => dashboardStore.charts.statusDistribution?.series ?? []);

onMounted(() => {
  dashboardStore.fetchDashboard();
});
</script>
