import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import * as adminApi from '@/api/adminApi';

export const useAdminAuthStore = defineStore('adminAuth', () => {
    const token = ref(null);
    const user = ref(null);

    const isAuthenticated = computed(() => !!token.value);
    const isRecommender = computed(() => user.value?.role === '200');
    const isApprover = computed(() => user.value?.role === '300' || user.value?.role === '400');
    const isSuperAdmin = computed(() => user.value?.role === '400');
    const fullName = computed(() => {
        if (!user.value) return '';
        return `${user.value.title ?? ''} ${user.value.surname ?? ''} ${user.value.firstname ?? ''}`.trim();
    });

    async function login(credentials) {
        const { data } = await adminApi.login(credentials);
        token.value = data.token;
        user.value = data.admin ?? data.user;
        localStorage.setItem('admin_token', data.token);
        return data;
    }

    async function register(payload) {
        const { data } = await adminApi.register(payload);
        return data;
    }

    async function logout() {
        try {
            await adminApi.logout();
        } finally {
            token.value = null;
            user.value = null;
            localStorage.removeItem('admin_token');
        }
    }

    async function fetchUser() {
        const { data } = await adminApi.getMe();
        user.value = data.admin ?? data.user ?? data;
        return user.value;
    }

    async function resetPassword(payload) {
        const { data } = await adminApi.resetPassword(payload);
        return data;
    }

    return {
        token,
        user,
        isAuthenticated,
        isRecommender,
        isApprover,
        isSuperAdmin,
        fullName,
        login,
        register,
        logout,
        fetchUser,
        resetPassword,
    };
}, {
    persist: {
        paths: ['token', 'user'],
    },
});
