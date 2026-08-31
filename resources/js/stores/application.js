import { ref } from 'vue';
import { defineStore } from 'pinia';
import * as applicantApi from '@/api/applicantApi';

export const useApplicationStore = defineStore('application', () => {
    const officialApplications = ref([]);
    const studentApplications = ref([]);
    const stats = ref({ successful: 0, pending: 0, failed: 0 });
    const destinations = ref([]);
    const pricing = ref([]);
    const loading = ref(false);

    async function fetchOfficialApplications() {
        loading.value = true;
        try {
            const { data } = await applicantApi.getMyApplications();
            officialApplications.value = data.data ?? data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchStudentApplications() {
        loading.value = true;
        try {
            const { data } = await applicantApi.getMyStudentApplications();
            studentApplications.value = data.data ?? data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchStats() {
        const { data } = await applicantApi.getStats();
        stats.value = data.data ?? data;
    }

    async function checkAvailability() {
        const { data } = await applicantApi.checkAvailability({});
        return data;
    }

    async function fetchDestinations() {
        const { data } = await applicantApi.getDestinations();
        const result = data.data ?? data;
        if (result.destinations) {
            destinations.value = result.destinations;
            pricing.value = result.pricing || [];
        } else {
            destinations.value = result;
        }
    }

    function getTypeAmount(type) {
        const item = pricing.value.find(p => p.type === type);
        return item ? Number(item.amount) : 0;
    }

    async function submitApplication(formData) {
        const { data } = await applicantApi.submitApplication(formData);
        return data;
    }

    async function editApplication(payload) {
        const { data } = await applicantApi.editApplication(payload);
        return data;
    }

    return {
        officialApplications,
        studentApplications,
        stats,
        destinations,
        pricing,
        loading,
        fetchOfficialApplications,
        fetchStudentApplications,
        fetchStats,
        checkAvailability,
        fetchDestinations,
        getTypeAmount,
        submitApplication,
        editApplication,
    };
});
