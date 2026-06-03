import { ref } from 'vue';
import { defineStore } from 'pinia';
import * as adminApi from '@/api/adminApi';

export const useDashboardStore = defineStore('dashboard', () => {
    const services = ref({});
    const revenue = ref({ transcript: '0', degree: '0', total: '0' });
    const charts = ref({
        monthlyLabels: [],
        monthlySeries: [],
        statusDistribution: { labels: [], series: [] },
    });
    const activities = ref([]);
    const locations = ref([]);
    const loading = ref(false);

    async function fetchDashboard() {
        loading.value = true;
        try {
            const { data } = await adminApi.getDashboard();
            const d = data.data ?? data;
            services.value = d.services ?? {};
            revenue.value = d.revenue ?? { transcript: '0', degree: '0', total: '0' };
            charts.value = d.charts ?? {
                monthlyLabels: [],
                monthlySeries: [],
                statusDistribution: { labels: [], series: [] },
            };
            activities.value = d.recentActivities ?? [];
        } finally {
            loading.value = false;
        }
    }

    async function fetchLocations() {
        const { data } = await adminApi.getTranscriptLocations();
        locations.value = data.data ?? data;
    }

    return {
        services,
        revenue,
        charts,
        activities,
        locations,
        loading,
        fetchDashboard,
        fetchLocations,
    };
});
