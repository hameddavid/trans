import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useAdminAuthStore } from '@/stores/adminAuth';

const routes = [
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', name: 'home', component: () => import('@/pages/public/HomePage.vue') },
      { path: 'verify-transcript', name: 'verify-transcript', component: () => import('@/pages/public/TranscriptVerificationPage.vue') },
      { path: 'verify-degree', name: 'verify-degree', component: () => import('@/pages/public/DegreeVerificationPage.vue') },
    ],
  },

  // Applicant auth (guest only)
  {
    path: '/applicant/login',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'applicant' },
    children: [
      { path: '', name: 'applicant-login', component: () => import('@/pages/applicant/LoginPage.vue') },
    ],
  },
  {
    path: '/applicant/register',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'applicant' },
    children: [
      { path: '', name: 'applicant-register', component: () => import('@/pages/applicant/RegisterPage.vue') },
    ],
  },
  {
    path: '/applicant/forgot-password',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'applicant' },
    children: [
      { path: '', name: 'applicant-forgot-password', component: () => import('@/pages/applicant/ForgotPasswordPage.vue') },
    ],
  },
  {
    path: '/applicant/forgot-matric',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'applicant' },
    children: [
      { path: '', name: 'applicant-forgot-matric', component: () => import('@/pages/applicant/ForgotMatricPage.vue') },
    ],
  },
  {
    path: '/applicant/reset-password',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'applicant' },
    children: [
      { path: '', name: 'applicant-reset-password', component: () => import('@/pages/applicant/ResetPasswordPage.vue') },
    ],
  },

  // Admin auth (guest only)
  {
    path: '/toptop/login',
    component: () => import('@/layouts/AuthLayout.vue'),
    meta: { guestOnly: true, guard: 'admin' },
    children: [
      { path: '', name: 'admin-login', component: () => import('@/pages/admin/LoginPage.vue') },
    ],
  },

  // Applicant authenticated
  {
    path: '/applicant',
    component: () => import('@/layouts/ApplicantLayout.vue'),
    meta: { requiresAuth: true, guard: 'applicant' },
    children: [
      { path: 'dashboard', name: 'applicant-dashboard', component: () => import('@/pages/applicant/DashboardPage.vue'), meta: { title: 'Dashboard' } },
      { path: 'apply', name: 'applicant-apply', component: () => import('@/pages/applicant/ApplyPage.vue'), meta: { title: 'Apply for Transcript' } },
      { path: 'my-applications', name: 'applicant-applications', component: () => import('@/pages/applicant/ApplicationsPage.vue'), meta: { title: 'My Applications' } },
      { path: 'my-payments', name: 'applicant-payments', component: () => import('@/pages/applicant/PaymentsPage.vue'), meta: { title: 'My Payments' } },
      { path: 'degree-verification', name: 'applicant-degree-verification', component: () => import('@/pages/applicant/DegreeVerificationPage.vue'), meta: { title: 'Degree Verification' } },
      { path: 'settings', name: 'applicant-settings', component: () => import('@/pages/applicant/SettingsPage.vue'), meta: { title: 'Settings' } },
    ],
  },

  // Admin authenticated
  {
    path: '/toptop',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, guard: 'admin' },
    children: [
      { path: 'dashboard', name: 'admin-dashboard', component: () => import('@/pages/admin/DashboardPage.vue'), meta: { title: 'Dashboard' } },

      { path: 'applications/pending', name: 'admin-pending-official', component: () => import('@/pages/admin/applications/PendingPage.vue'), meta: { title: 'Pending Applications' } },
      { path: 'applications/recommended', name: 'admin-recommended-official', component: () => import('@/pages/admin/applications/RecommendedPage.vue'), meta: { title: 'Recommended Applications' } },
      { path: 'applications/approved', name: 'admin-approved-official', component: () => import('@/pages/admin/applications/ApprovedPage.vue'), meta: { title: 'Approved Applications' } },
      { path: 'applications/failed', name: 'admin-failed-official', component: () => import('@/pages/admin/applications/FailedPage.vue'), meta: { title: 'Failed Applications' } },

      { path: 'student-applications/pending', name: 'admin-pending-student', component: () => import('@/pages/admin/student-applications/PendingPage.vue'), meta: { title: 'Pending Student Applications' } },
      { path: 'student-applications/recommended', name: 'admin-recommended-student', component: () => import('@/pages/admin/student-applications/RecommendedPage.vue'), meta: { title: 'Recommended Student Applications' } },
      { path: 'student-applications/approved', name: 'admin-approved-student', component: () => import('@/pages/admin/student-applications/ApprovedPage.vue'), meta: { title: 'Approved Student Applications' } },

      { path: 'degree/pending', name: 'admin-pending-degree', component: () => import('@/pages/admin/degree/PendingPage.vue'), meta: { title: 'Pending Degree Verifications' } },
      { path: 'degree/recommended', name: 'admin-recommended-degree', component: () => import('@/pages/admin/degree/RecommendedPage.vue'), meta: { title: 'Recommended Degree Verifications' } },
      { path: 'degree/approved', name: 'admin-approved-degree', component: () => import('@/pages/admin/degree/ApprovedPage.vue'), meta: { title: 'Approved Degree Verifications' } },

      { path: 'applicants', name: 'admin-applicants', component: () => import('@/pages/admin/ApplicantsPage.vue'), meta: { title: 'Applicants' } },
      { path: 'complaints', name: 'admin-complaints', component: () => import('@/pages/admin/ComplaintsPage.vue'), meta: { title: 'Student Complaints' } },
      { path: 'payments', name: 'admin-payments', component: () => import('@/pages/admin/PaymentsPage.vue'), meta: { title: 'Payments' } },
      { path: 'generated-transcripts', name: 'admin-generated-transcripts', component: () => import('@/pages/admin/GeneratedTranscriptsPage.vue'), meta: { title: 'Generated Transcripts' } },
      { path: 'forgot-matric', name: 'admin-forgot-matric', component: () => import('@/pages/admin/ForgotMatricPage.vue'), meta: { title: 'Forgot Matric' } },
      { path: 'generate-transcript', name: 'admin-generate-transcript', component: () => import('@/pages/admin/GenerateTranscriptPage.vue'), meta: { title: 'Generate Transcript' } },
      { path: 'signatories', name: 'admin-signatories', component: () => import('@/pages/admin/SignatoriesPage.vue'), meta: { title: 'Signatories' } },
      { path: 'users', name: 'admin-users', component: () => import('@/pages/admin/AdminUsersPage.vue'), meta: { title: 'Admin Users' } },
      { path: 'pricing', name: 'admin-pricing', component: () => import('@/pages/admin/PricingPage.vue'), meta: { title: 'Payment Pricing' } },
      { path: 'settings', name: 'admin-settings', component: () => import('@/pages/admin/SettingsPage.vue'), meta: { title: 'Settings' } },
    ],
  },

  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFound.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(r => r.meta.requiresAuth);
  const guestOnly = to.matched.some(r => r.meta.guestOnly);
  const guard = to.matched.find(r => r.meta.guard)?.meta.guard;

  if (requiresAuth && guard === 'applicant') {
    const authStore = useAuthStore();
    if (!authStore.isAuthenticated) {
      return next({ name: 'applicant-login', query: { redirect: to.fullPath } });
    }
  }

  if (requiresAuth && guard === 'admin') {
    const adminAuthStore = useAdminAuthStore();
    if (!adminAuthStore.isAuthenticated) {
      return next({ name: 'admin-login', query: { redirect: to.fullPath } });
    }
  }

  if (guestOnly && guard === 'applicant') {
    const authStore = useAuthStore();
    if (authStore.isAuthenticated) {
      return next({ name: 'applicant-dashboard' });
    }
  }

  if (guestOnly && guard === 'admin') {
    const adminAuthStore = useAdminAuthStore();
    if (adminAuthStore.isAuthenticated) {
      return next({ name: 'admin-dashboard' });
    }
  }

  next();
});

export default router;
