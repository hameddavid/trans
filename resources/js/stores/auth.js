import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import * as applicantApi from '@/api/applicantApi';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(null);
    const user = ref(null);

    const isAuthenticated = computed(() => !!token.value);
    const fullName = computed(() => {
        if (!user.value) return '';
        return `${user.value.surname ?? ''} ${user.value.firstname ?? ''}`.trim();
    });

    async function login(credentials) {
        const { data } = await applicantApi.login(credentials);
        token.value = data.token;
        user.value = data.applicant ?? data.user;
        localStorage.setItem('applicant_token', data.token);
        return data;
    }

    async function register(payload) {
        const { data } = await applicantApi.register(payload);
        token.value = data.token;
        user.value = data.applicant ?? data.user;
        localStorage.setItem('applicant_token', data.token);
        return data;
    }

    async function logout() {
        try {
            await applicantApi.logout();
        } finally {
            token.value = null;
            user.value = null;
            localStorage.removeItem('applicant_token');
        }
    }

    async function fetchUser() {
        const { data } = await applicantApi.getMe();
        user.value = data.applicant ?? data.user ?? data;
        return user.value;
    }

    async function resetPassword(payload) {
        const { data } = await applicantApi.resetPassword(payload);
        return data;
    }

    async function forgotPassword(payload) {
        const { data } = await applicantApi.forgotPassword(payload);
        return data;
    }

    async function forgotMatric(payload) {
        const { data } = await applicantApi.forgotMatric(payload);
        return data;
    }

    return {
        token,
        user,
        isAuthenticated,
        fullName,
        login,
        register,
        logout,
        fetchUser,
        resetPassword,
        forgotPassword,
        forgotMatric,
    };
}, {
    persist: {
        paths: ['token', 'user'],
    },
});
