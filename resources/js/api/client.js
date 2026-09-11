import axios from 'axios';

const TOKEN_STORAGE_KEY = 'turno_api_token';

export const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

export function getStoredToken() {
    return window.localStorage.getItem(TOKEN_STORAGE_KEY);
}

export function storeToken(token) {
    window.localStorage.setItem(TOKEN_STORAGE_KEY, token);
    setAuthToken(token);
}

export function clearStoredToken() {
    window.localStorage.removeItem(TOKEN_STORAGE_KEY);
    setAuthToken(null);
}

export function setAuthToken(token) {
    if (token) {
        apiClient.defaults.headers.common.Authorization = `Bearer ${token}`;
        return;
    }

    delete apiClient.defaults.headers.common.Authorization;
}

setAuthToken(getStoredToken());
