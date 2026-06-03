import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useAdminAuthStore } from '@/stores/adminAuth';

export function useAuth(guard = 'applicant') {
  const store = guard === 'admin' ? useAdminAuthStore() : useAuthStore();
  const tokenKey = `${guard}_token`;

  const user = computed(() => store.user);
  const token = computed(() => store.token);
  const isAuthenticated = computed(() => store.isAuthenticated);

  async function login(credentials) {
    return store.login(credentials);
  }

  async function register(payload) {
    return store.register(payload);
  }

  async function logout() {
    return store.logout();
  }

  return {
    user,
    token,
    isAuthenticated,
    tokenKey,
    login,
    register,
    logout,
  };
}
