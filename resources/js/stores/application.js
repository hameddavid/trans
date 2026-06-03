import { ref } from 'vue';
import { defineStore } from 'pinia';
import * as applicantApi from '@/api/applicantApi';

export const useApplicationStore = defineStore('application', () => {
    const officialApplications = ref([]);
    const studentApplications = ref([]);
    const stats = ref({ successful: 0, pending: 0, failed: 0 });
    const destinations = ref([]);
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
        destinations.value = data.data ?? data;
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
        loading,
        fetchOfficialApplications,
        fetchStudentApplications,
        fetchStats,
        checkAvailability,
        fetchDestinations,
        submitApplication,
        editApplication,
    };
});
