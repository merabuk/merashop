import axios, { AxiosInstance, AxiosError, AxiosResponse } from 'axios';
import { HTTP_HEADERS } from '@shared/config/http.ts';
import { DOM_DATA_ATTRIBUTES } from '@shared/config/dom.ts';
import { getActiveLocale } from '@shared/services/localeProvider.ts';
import type { ApiError } from '@shared/types/error.ts';
import type { AuthResponse } from '@shared/types/admin/auth.ts';
import { useSessionStore } from '@shared/stores/admin/useSessionStore.ts';
import { IDENTITY_ACCESS_API_ENDPOINTS } from '@identity-access/paths/admin/api.ts';
import { GrantType } from '@identity-access/types/grant_type.enum.ts';
import { useToastStore } from "@shared/stores/admin/useToastStore.ts";
import { i18n } from '@shared/i18n';

const rootElement = document.getElementById('app');
const currentTraceId = rootElement?.dataset[DOM_DATA_ATTRIBUTES.TRACE_ID] || 'no-trace-id';

const SECONDS_EXPIRATION_THRESHOLD = 30;

const adminApiClient: AxiosInstance = axios.create({
    baseURL: '/',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        [HTTP_HEADERS.ACCEPT_LANGUAGE]: getActiveLocale(),
        [HTTP_HEADERS.TRACE_ID]: currentTraceId,
        [HTTP_HEADERS.REQUESTED_WITH]: 'XMLHttpRequest'
    }
});

interface QueuedPromise {
    resolve: (token: string | null) => void;
    reject: (error: unknown) => void;
}

let isRefreshing = false;
let failedQueue: QueuedPromise[] = [];

const processQueue = (error: unknown, token: string | null = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

adminApiClient.interceptors.request.use(async (config) => {
    if (config.url?.includes('/auth/token')) {
        return config;
    }

    const session = useSessionStore();
    const token = localStorage.getItem('access_token');
    const refreshToken = localStorage.getItem('refresh_token');

    if (token && refreshToken && session.userPayload) {
        const isExpiringSoon = session.userPayload.exp * 1000 - Date.now() < SECONDS_EXPIRATION_THRESHOLD * 1000;

        if (isExpiringSoon) {
            if (isRefreshing) {
                return new Promise((resolve, reject) => {
                    failedQueue.push({ resolve, reject });
                }).then(newToken => {
                    config.headers.Authorization = `Bearer ${newToken}`;

                    return config;
                }).catch(err => Promise.reject(err));
            }

            isRefreshing = true;
            try {
                const { data } = await axios.post<AuthResponse>(IDENTITY_ACCESS_API_ENDPOINTS.ADMIN.AUTH.TOKEN, {
                    grant_type: GrantType.RefreshToken,
                    refresh_token: refreshToken
                });
                session.setAuth(data);
                config.headers.Authorization = `Bearer ${data.access_token}`;

                processQueue(null, data.access_token);

                return config;
            } catch (err) {
                processQueue(err, null);
                session.logout();

                return Promise.reject(err);
            } finally {
                isRefreshing = false;
            }
        }
    }

    if (token && !config.headers.Authorization) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

adminApiClient.interceptors.response.use(
    (response: AxiosResponse) => response,
    async (error: AxiosError<ApiError>) => {
        const t = i18n.global.t as (key: string) => string;
        const toaster = useToastStore();
        const session = useSessionStore();
        const { response } = error;
        const status = response?.status;
        const apiErrorData = response?.data as ApiError;

        if (status === 401) {
            toaster.error(t('common.errors.session_expired'));
            session.logout();
        }
        if (status === 403) {
            toaster.error(t('common.errors.access_denied'));
        }
        if (status === 422) {
            toaster.error(apiErrorData.message);
            return Promise.reject(apiErrorData);
        }
        if (undefined === status || status >= 500) {
            toaster.error(t('common.errors.server_error'));
        }

        return Promise.reject(apiErrorData || error);
    }
);

export default adminApiClient;
