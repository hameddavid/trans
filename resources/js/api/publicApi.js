import api from './axios';

export const verifyTranscript = (data) => api.post('/public/verify-transcript', data);
export const verifyDegree = (data) => api.post('/public/degree-verification', data);
export const getProgrammes = () => api.get('/public/programmes');
export const getProgrammeList = () => api.get('/public/programme-list');
export const getDestinations = () => api.get('/public/destinations');
