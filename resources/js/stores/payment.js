import { ref } from 'vue';
import { defineStore } from 'pinia';
import * as applicantApi from '@/api/applicantApi';

export const usePaymentStore = defineStore('payment', () => {
    const payments = ref([]);
    const loading = ref(false);

    async function fetchMyPayments() {
        loading.value = true;
        try {
            const { data } = await applicantApi.getMyPayments();
            payments.value = data.data ?? data;
        } finally {
            loading.value = false;
        }
    }

    async function checkPendingRRR(payload) {
        const { data } = await applicantApi.checkPendingRRR(payload);
        return data;
    }

    async function logTransaction(payload) {
        const { data } = await applicantApi.logTransaction(payload);
        return data;
    }

    async function fetchGatewayConfig(params) {
        const { data } = await applicantApi.getGatewayConfig(params);
        return data;
    }

    async function updatePayment(payload) {
        const { data } = await applicantApi.updatePayment(payload);
        return data;
    }

    async function requeryTransaction(payload) {
        const { data } = await applicantApi.requery(payload);
        return data;
    }

    async function remitaBankPayment(payload) {
        const { data } = await applicantApi.remitaBank(payload);
        return data;
    }

    async function degreeCheckPending(payload) {
        const { data } = await applicantApi.degreePay.checkPendingRRR(payload);
        return data;
    }

    async function degreeLogTransaction(payload) {
        const { data } = await applicantApi.degreePay.logTransaction(payload);
        return data;
    }

    async function degreeGatewayConfig(params) {
        const { data } = await applicantApi.degreePay.getGatewayConfig(params);
        return data;
    }

    async function degreeUpdatePayment(payload) {
        const { data } = await applicantApi.degreePay.updatePayment(payload);
        return data;
    }

    async function degreeRequery(payload) {
        const { data } = await applicantApi.degreePay.requery(payload);
        return data;
    }

    return {
        payments,
        loading,
        fetchMyPayments,
        checkPendingRRR,
        logTransaction,
        fetchGatewayConfig,
        updatePayment,
        requeryTransaction,
        remitaBankPayment,
        degreeCheckPending,
        degreeLogTransaction,
        degreeGatewayConfig,
        degreeUpdatePayment,
        degreeRequery,
    };
});
