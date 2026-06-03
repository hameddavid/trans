import api from './axios';

export const login = (credentials) => api.post('/admin/login', credentials);
export const register = (data) => api.post('/admin/register', data);
export const getMe = () => api.get('/admin/me');
export const logout = () => api.post('/admin/logout');
export const resetPassword = (data) => api.post('/admin/reset-password', data);

export const getDashboard = () => api.get('/admin/dashboard');
export const getTranscriptActivities = () => api.get('/admin/transcript-activities');
export const getTranscriptLocations = () => api.get('/admin/transcript-locations');

export const getPendingOfficial = (page = 1, perPage = 15) => api.get('/admin/applications/pending-official', { params: { page, per_page: perPage } });
export const getRecommendedOfficial = (page = 1, perPage = 15) => api.get('/admin/applications/recommended-official', { params: { page, per_page: perPage } });
export const getApprovedOfficial = (page = 1, perPage = 15) => api.get('/admin/applications/approved-official', { params: { page, per_page: perPage } });
export const getFailedOfficial = (page = 1, perPage = 15) => api.get('/admin/applications/failed-official', { params: { page, per_page: perPage } });
export const getPendingStudent = (page = 1, perPage = 15) => api.get('/admin/applications/pending-student', { params: { page, per_page: perPage } });
export const getRecommendedStudent = (page = 1, perPage = 15) => api.get('/admin/applications/recommended-student', { params: { page, per_page: perPage } });
export const getApprovedStudent = (page = 1, perPage = 15) => api.get('/admin/applications/approved-student', { params: { page, per_page: perPage } });

export const recommend = (data) => api.post('/admin/applications/recommend', data);
export const deRecommend = (data) => api.post('/admin/applications/de-recommend', data);
export const approve = (data) => api.post('/admin/applications/approve', data);
export const disapprove = (data) => api.post('/admin/applications/disapprove', data);
export const regenerate = (data) => api.post('/admin/applications/regenerate', data);
export const sendCorrections = (data) => api.post('/admin/applications/send-corrections', data);
export const getTranscriptHtml = (type, id) => api.get(`/admin/applications/transcript-html/${type}/${id}`);
export const downloadApproved = (data) => api.post('/admin/applications/download-approved', data, { responseType: 'blob' });
export const submitAdminApp = (data) => api.post('/admin/applications/submit-admin-app', data);
export const downloadAdminApp = (data) => api.post('/admin/applications/download-admin-app', data, { responseType: 'blob' });

export const getPendingDegree = (page = 1, perPage = 15) => api.get('/admin/degree-verification/pending', { params: { page, per_page: perPage } });
export const getRecommendedDegree = (page = 1, perPage = 15) => api.get('/admin/degree-verification/recommended', { params: { page, per_page: perPage } });
export const getApprovedDegree = (page = 1, perPage = 15) => api.get('/admin/degree-verification/approved', { params: { page, per_page: perPage } });
export const treatDegree = (data) => api.post('/admin/degree-verification/treat', data);
export const recommendDegree = (data) => api.post('/admin/degree-verification/recommend', data);
export const approveDegree = (data) => api.post('/admin/degree-verification/approve', data);
export const viewDegreeDocument = (path) => api.get(`/admin/degree-verification/view-document/${path}`, { responseType: 'blob' });

export const getApplicants = (page = 1, perPage = 15) => api.get('/admin/applicants', { params: { page, per_page: perPage } });
export const updateApplicant = (data) => api.post('/admin/applicants/update', data);
export const getComplaints = (page = 1, perPage = 15) => api.get('/admin/complaints', { params: { page, per_page: perPage } });
export const respondToComplaint = (data) => api.post('/admin/complaints/respond', data);
export const getPayments = (page = 1, perPage = 15) => api.get('/admin/payments', { params: { page, per_page: perPage } });
export const getGeneratedTranscripts = (page = 1, perPage = 15) => api.get('/admin/generated-transcripts', { params: { page, per_page: perPage } });
export const getForgotMatricRequests = (page = 1, perPage = 15) => api.get('/admin/forgot-matric-requests', { params: { page, per_page: perPage } });
export const treatForgotMatric = (data) => api.post('/admin/treat-forgot-matric', data);
