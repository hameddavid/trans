import { ref, reactive } from 'vue';
import { defineStore } from 'pinia';
import * as adminApi from '@/api/adminApi';
import * as applicantApi from '@/api/applicantApi';

export const useDegreeVerificationStore = defineStore('degreeVerification', () => {
    const pending = ref([]);
    const recommended = ref([]);
    const approved = ref([]);
    const loading = ref(false);

    const pagination = reactive({
        pending: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        recommended: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
        approved: { currentPage: 1, lastPage: 1, total: 0, perPage: 15 },
    });

    function extractPagination(response, key) {
        const meta = response.meta || response;
        pagination[key].currentPage = meta.current_page ?? 1;
        pagination[key].lastPage = meta.last_page ?? 1;
        pagination[key].total = meta.total ?? 0;
        pagination[key].perPage = meta.per_page ?? 15;
    }

    async function fetchPending(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.pending.perPage;
            const { data } = await adminApi.getPendingDegree(page, pp);
            pending.value = data.data ?? data;
            extractPagination(data, 'pending');
        } finally {
            loading.value = false;
        }
    }

    async function fetchRecommended(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.recommended.perPage;
            const { data } = await adminApi.getRecommendedDegree(page, pp);
            recommended.value = data.data ?? data;
            extractPagination(data, 'recommended');
        } finally {
            loading.value = false;
        }
    }

    async function fetchApproved(page = 1, perPage) {
        loading.value = true;
        try {
            const pp = perPage ?? pagination.approved.perPage;
            const { data } = await adminApi.getApprovedDegree(page, pp);
            approved.value = data.data ?? data;
            extractPagination(data, 'approved');
        } finally {
            loading.value = false;
        }
    }

    async function treatDegree(payload) {
        const { data } = await adminApi.treatDegree(payload);
        return data;
    }

    async function recommendDegree(payload) {
        const { data } = await adminApi.recommendDegree(payload);
        return data;
    }

    async function approveDegree(payload) {
        const { data } = await adminApi.approveDegree(payload);
        return data;
    }

    async function submitVerification(payload) {
        const { data } = await applicantApi.submitDegreeVerification(payload);
        return data;
    }

    return {
        pending,
        recommended,
        approved,
        loading,
        pagination,
        fetchPending,
        fetchRecommended,
        fetchApproved,
        treatDegree,
        recommendDegree,
        approveDegree,
        submitVerification,
    };
});
