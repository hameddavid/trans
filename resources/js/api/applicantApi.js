import api from './axios';

export const login = (credentials) => api.post('/applicant/login', credentials);
export const register = (data) => api.post('/applicant/register', data);
export const forgotPassword = (data) => api.post('/applicant/forgot-password', data);
export const forgotMatric = (data) => api.post('/applicant/forgot-matric', data);
export const resetPassword = (data) => api.post('/applicant/reset-password', data);
export const resetPasswordWithToken = (data) => api.post('/applicant/reset-password-with-token', data);
export const getMe = () => api.get('/applicant/me');
export const logout = () => api.post('/applicant/logout');

export const checkAvailability = (params) => api.get('/applicant/check-availability', { params });
export const getDestinations = () => api.get('/public/destinations');
export const submitApplication = (formData) =>
    api.post('/applicant/submit-application', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
export const getMyApplications = () => api.get('/applicant/my-official-applications');
export const getMyStudentApplications = () => api.get('/applicant/my-student-applications');
export const getStats = () => api.get('/applicant/stats');
export const editApplication = (data) => api.post('/applicant/edit-application', data);
export const submitComplaint = (data) => api.post('/applicant/submit-complaint', data);
export const getMyComplaints = () => api.get('/applicant/my-complaints');

export const initiatePayment = (data) => api.post('/applicant/payment/initiate', data);
export const verifyPayment = (data) => api.post('/applicant/payment/verify', data);
export const checkPendingRRR = (data) => api.post('/applicant/payment/check-pending-rrr', data);
export const logTransaction = (data) => api.post('/applicant/payment/log-transaction', data);
export const getGatewayConfig = (params) => api.get('/applicant/payment/gateway-config', { params });
export const updatePayment = (data) => api.post('/applicant/payment/update-payment', data);
export const requery = (data) => api.post('/applicant/payment/re-query', data);
export const remitaBank = (data) => api.post('/applicant/payment/remita-bank-callback', data);
export const getMyPayments = () => api.get('/applicant/my-payments');

export const degreePay = {
    checkPendingRRR: (data) => api.post('/applicant/degree-payment/check-pending-rrr', data),
    logTransaction: (data) => api.post('/applicant/degree-payment/log-transaction', data),
    getGatewayConfig: (params) => api.get('/applicant/degree-payment/gateway-config', { params }),
    updatePayment: (data) => api.post('/applicant/degree-payment/update-payment', data),
    requery: (data) => api.post('/applicant/degree-payment/re-query', data),
    remitaBank: (data) => api.post('/applicant/degree-payment/remita-bank-callback', data),
};

export const submitDegreeVerification = (data) => api.post('/public/degree-verification', data);
