import axios, { AxiosInstance, AxiosError, AxiosResponse } from 'axios';
import { HTTP_HEADERS, DOM_DATA_ATTRIBUTES } from '@shared/constants';
import type { ApiError } from '@shared/types';

const rootElement = document.getElementById('app');
const currentTraceId = rootElement?.dataset[DOM_DATA_ATTRIBUTES.TRACE_ID] || 'no-trace-id';

const apiClient: AxiosInstance = axios.create({
    baseURL: '/',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        [HTTP_HEADERS.TRACE_ID]: currentTraceId,
        [HTTP_HEADERS.REQUESTED_WITH]: 'XMLHttpRequest'
    }
});

apiClient.interceptors.request.use((config) => {
    // If JWT becomes available in the future, we'll add it here
    // const token = localStorage.getItem('auth_token');
    // if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

apiClient.interceptors.response.use(
    (response: AxiosResponse) => response,

    (error: AxiosError<ApiError>) => {
        const { response } = error;

        if (response) {
            const apiErrorData = response.data;
            const status = response.status;

            console.error(
                `[API Error] Status: ${status} | Code: ${apiErrorData.errorCode} | TraceId: ${currentTraceId}`,
                apiErrorData
            );

            switch (status) {
                case 401:
                    // window.location.href = '/login';
                    break;
                case 403:
                    console.warn('Access denied (Forbidden)');
                    break;
                case 422:
                    console.warn('Field validation error:', apiErrorData.violations);
                    break;
                case 500:
                    console.error('Critical server error');
                    break;
            }

            return Promise.reject(apiErrorData);
        } else if (error.request) {
            console.error('[API Error] The server is not responding. Please check your connection.');
        } else {
            console.error('[API Error] Message:', error.message);
        }

        return Promise.reject(error);
    }
);

export default apiClient;
