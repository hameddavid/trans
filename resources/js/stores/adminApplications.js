import { ref, reactive } from 'vue';
import { defineStore } from 'pinia';
import * as adminApi from '@/api/adminApi';

export const useAdminApplicationsStore = defineStore('adminApplications', () => {
    const pendingOfficial = ref([]);
    const recommendedOfficial = ref([]);
    const approvedOfficial = ref([]);
    const failedOfficial = ref([]);
    const pendingStudent = ref([]);
    const recommendedStudent = ref([]);
    const approvedStudent = ref([]);
    const loading = ref(false);

    const pagination = reactive({
        pendingOfficial: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        recommendedOfficial: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        approvedOfficial: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        failedOfficial: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        pendingStudent: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        recommendedStudent: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        approvedStudent: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
    });

    function extractPagination(response, key) {
        const meta = response.meta || response;
        pagination[key].currentPage = meta.current_page ?? 1;
        pagination[key].lastPage = meta.last_page ?? 1;
        pagination[key].total = meta.total ?? 0;
        pagination[key].perPage = meta.per_page ?? 15;
    }

    async function fetchPendingOfficial(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.pendingOfficial.perPage;
            const { data } = await adminApi.getPendingOfficial(page, pp);
            pendingOfficial.value = data.data ?? data;
            extractPagination(data, 'pendingOfficial');
        } finally {
            loading.value = false;
        }
    }

    async function fetchRecommendedOfficial(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.recommendedOfficial.perPage;
            const { data } = await adminApi.getRecommendedOfficial(page, pp);
            recommendedOfficial.value = data.data ?? data;
            extractPagination(data, 'recommendedOfficial');
        } finally {
            loading.value = false;
        }
    }

    async function fetchApprovedOfficial(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.approvedOfficial.perPage;
            const { data } = await adminApi.getApprovedOfficial(page, pp);
            approvedOfficial.value = data.data ?? data;
            extractPagination(data, 'approvedOfficial');
        } finally {
            loading.value = false;
        }
    }

    async function fetchFailedOfficial(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.failedOfficial.perPage;
            const { data } = await adminApi.getFailedOfficial(page, pp);
            failedOfficial.value = data.data ?? data;
            extractPagination(data, 'failedOfficial');
        } finally {
            loading.value = false;
        }
    }

    async function fetchPendingStudent(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.pendingStudent.perPage;
            const { data } = await adminApi.getPendingStudent(page, pp);
            pendingStudent.value = data.data ?? data;
            extractPagination(data, 'pendingStudent');
        } finally {
            loading.value = false;
        }
    }

    async function fetchRecommendedStudent(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.recommendedStudent.perPage;
            const { data } = await adminApi.getRecommendedStudent(page, pp);
            recommendedStudent.value = data.data ?? data;
            extractPagination(data, 'recommendedStudent');
        } finally {
            loading.value = false;
        }
    }

    async function fetchApprovedStudent(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.approvedStudent.perPage;
            const { data } = await adminApi.getApprovedStudent(page, pp);
            approvedStudent.value = data.data ?? data;
            extractPagination(data, 'approvedStudent');
        } finally {
            loading.value = false;
        }
    }

    async function recommendApp(payload) {
        const { data } = await adminApi.recommend(payload);
        return data;
    }

    async function deRecommendApp(payload) {
        const { data } = await adminApi.deRecommend(payload);
        return data;
    }

    async function approveApp(payload) {
        const { data } = await adminApi.approve(payload);
        return data;
    }

    async function disapproveApp(payload) {
        const { data } = await adminApi.disapprove(payload);
        return data;
    }

    async function regenerateTranscript(payload) {
        const { data } = await adminApi.regenerate(payload);
        return data;
    }

    async function sendCorrections(payload) {
        const { data } = await adminApi.sendCorrections(payload);
        return data;
    }

    async function downloadApproved(payload) {
        return adminApi.downloadApproved(payload);
    }

    async function submitAdminApp(payload) {
        const { data } = await adminApi.submitAdminApp(payload);
        return data;
    }

    return {
        pendingOfficial,
        recommendedOfficial,
        approvedOfficial,
        failedOfficial,
        pendingStudent,
        recommendedStudent,
        approvedStudent,
        loading,
        pagination,
        fetchPendingOfficial,
        fetchRecommendedOfficial,
        fetchApprovedOfficial,
        fetchFailedOfficial,
        fetchPendingStudent,
        fetchRecommendedStudent,
        fetchApprovedStudent,
        recommendApp,
        deRecommendApp,
        approveApp,
        disapproveApp,
        regenerateTranscript,
        sendCorrections,
        downloadApproved,
        submitAdminApp,
    };
});
