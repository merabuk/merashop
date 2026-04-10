<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden border-t-4 border-merashop-500">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">MeraShop <span class="text-merashop-500">Admin</span></h1>
                    <p class="text-gray-500">{{ $t('auth.admin.login_title') }}</p>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div v-if="errorMsg" class="bg-red-50 text-red-600 p-3 rounded text-sm border border-red-200">
                        {{ errorMsg }}
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('auth.admin.username') }} / Email</label>
                        <input v-model="form.username" type="text"
                               class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-merashop-500 outline-none transition"
                               :class="{'border-red-500': violations.username}" required>
                        <p v-if="violations.username" class="text-red-500 text-xs mt-1">{{ violations.username }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('auth.admin.password') }}</label>
                        <input v-model="form.password" type="password"
                               class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-merashop-500 outline-none transition"
                               :class="{'border-red-500': violations.password}" required>
                    </div>

                    <button type="submit" :disabled="loading"
                            class="w-full bg-merashop-500 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded transition disabled:opacity-50">
                        {{ loading ? $t('common.logging') : $t('common.login') }}
                    </button>
                </form>
            </div>
            <div class="bg-gray-50 px-8 py-4 text-center">
                <span class="text-xs text-gray-400 font-mono">TRACE: {{ session.traceId }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, reactive } from 'vue';
    import adminApiClient from '@shared/admin-api-client';
    import { useSessionStore } from '@shared/store/useSessionStore';
    import type { AuthResponse } from '@shared/types/auth';
    import type { ApiError } from '@shared/types/error';
    import { IDENTITY_ACCESS_API_ENDPOINTS } from "@identity-access/api/endpoints";
    import { ADMIN_WEB_ENDPOINTS } from "@shared/web/admin/endpoints";
    import { GrantType} from "@identity-access/types/grant_type.enum";

    const session = useSessionStore();
    const loading = ref(false);
    const errorMsg = ref('');
    const violations = reactive<Record<string, string>>({});

    const form = reactive({
        grant_type: GrantType.Password,
        username: '',
        password: ''
    });

    async function handleLogin() {
        loading.value = true;
        errorMsg.value = '';
        Object.keys(violations).forEach(k => delete violations[k]);

        try {
            const { data } = await adminApiClient.post<AuthResponse>(IDENTITY_ACCESS_API_ENDPOINTS.ADMIN.AUTH.TOKEN, form);
            session.setAuth(data);
            window.location.href = ADMIN_WEB_ENDPOINTS.DASHBOARD;
        } catch (e: unknown) {
            const err = e as ApiError;
            err.violations?.forEach(v => {
                violations[v.field] = v.message;
            });
        } finally {
            loading.value = false;
        }
    }
</script>
