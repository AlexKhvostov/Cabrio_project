import axios from 'axios';
import { getTelegramHeaders } from './telegram';

const api = axios.create({
  baseURL: import.meta.env.VITE_BACKEND_API_URL?.replace(/\/$/, '') + '/backend',
});

api.interceptors.request.use((config) => {
  config.headers = {
    ...(config.headers || {}),
    ...getTelegramHeaders(),
  } as any;
  return config;
});

export const getUsers = async () => {
  const res = await api.get('/routes/api.php', { params: { route: '/api/users' } });
  return res.data;
};

export const getCars = async () => {
  const res = await api.get('/routes/api.php', { params: { route: '/api/cars' } });
  return res.data;
};

export const getProfile = async () => {
  const res = await api.get('/routes/api.php', { params: { route: '/api/users/profile' } });
  return res.data;
};

export default api;
