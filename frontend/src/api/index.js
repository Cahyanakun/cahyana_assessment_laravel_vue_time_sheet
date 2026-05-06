import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export const getInitData = async (companyId = null) => {
  const params = companyId ? { company_id: companyId } : {};
  const response = await api.get('/init-data', { params });
  return response.data;
};

export const getTimeEntries = async (params = {}) => {
  const response = await api.get('/time-entries', { params });
  return response.data;
};

export const storeTimeEntry = async (entries) => {
  // Backend expects: { entries: [ { company_id, employee_id, ... }, ... ] }
  const response = await api.post('/time-entries', { entries });
  return response.data;
};

export const updateTimeEntry = async (id, data) => {
  const response = await api.put(`/time-entries/${id}`, data);
  return response.data;
};

export default api;
