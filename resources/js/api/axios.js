import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Accept': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const adminToken = localStorage.getItem('admin_token');
    const applicantToken = localStorage.getItem('applicant_token');

    const token = config.url?.startsWith('/admin') ? adminToken : applicantToken;

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const isAdmin = error.config?.url?.startsWith('/admin');
            if (isAdmin) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
            } else {
                localStorage.removeItem('applicant_token');
                localStorage.removeItem('applicant_user');
            }
        }
        return Promise.reject(error);
    }
);

export default api;
