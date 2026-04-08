import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { jwtDecode } from 'jwt-decode';
import type { JwtPayload, SessionUser, AuthResponse } from '@shared/types/auth';

export const useSessionStore = defineStore('session', () => {
    const accessToken = ref<string | null>(localStorage.getItem('access_token'));
    const userPayload = ref<JwtPayload | null>(null);
    const traceId = ref<string>('no-trace-id');

    const userSession = computed<SessionUser | null>(() => {
        const payload = userPayload.value;

        if (!payload) {
            return null;
        }

        return {
            identifier: payload.sub,
            roles: payload.roles,
            type: payload.sub_type
        };
    });

    const isAuthenticated = computed(() => !!accessToken.value);

    function initialize(data: { userJson: string | undefined; currentTraceId: string }) {
        traceId.value = data.currentTraceId;

        if (accessToken.value && !userPayload.value) {
            try {
                userPayload.value = jwtDecode<JwtPayload>(accessToken.value);
            } catch {
                logout();
            }
        }
    }

    function setAuth(data: AuthResponse) {
        accessToken.value = data.access_token;
        localStorage.setItem('access_token', data.access_token);
        localStorage.setItem('refresh_token', data.refresh_token);
        userPayload.value = jwtDecode<JwtPayload>(data.access_token);
    }

    function logout() {
        accessToken.value = null;
        userPayload.value = null;
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        window.location.href = '/admin/login';
    }

    return {
        accessToken,
        userPayload,
        traceId,
        userSession,
        isAuthenticated,
        initialize,
        setAuth,
        logout
    };
});
