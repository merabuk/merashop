<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto">
            <BaseBreadcrumbs :items="breadcrumbs" />

            <AttributeForm
                v-model:form="form"
                :violations="violations"
                :is-loading="isLoading"
                :is-update="false"
                :cancel-href="ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST"
                :needs-options="needsOptions"
                @submit="handleSubmit"
                @add-option="addOption"
                @remove-option="removeOption"
            />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
import BaseBreadcrumbs from '@shared/components/admin/UI/BaseBreadcrumbs.vue';
import AttributeForm from "@catalog/components/admin/Attribute/AttributeForm.vue";
import { BreadcrumbItem } from "@shared/types/admin/breadcrumb.interface";
import { IconEnum } from "@shared/types/admin/icon.enum";
import { useAttributeForm } from '@catalog/composables/admin/useAttributeForm';
import adminApiClient from '@shared/api/adminApiClient.ts';
import { AttributeCreateResponse } from "@catalog/types/admin/attribute.interface";
import { CATALOG_API_ENDPOINTS } from '@catalog/paths/admin/api.ts';
import { ADMIN_WEB_ENDPOINTS } from "@shared/paths/admin/web.ts";
import type { ApiError } from '@shared/types/error';
import { useToastStore } from '@shared/stores/admin/useToastStore.ts';

const { t } = useI18n();
const { form, violations, needsOptions, addOption, removeOption, validate } = useAttributeForm();
const isLoading = ref(false);
const breadcrumbs: BreadcrumbItem[] = [
    { label: t('catalog.title'), icon: IconEnum.Catalog },
    { label: t('catalog.attributes.title'), href: ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST, icon: IconEnum.Attributes },
    { label: t('catalog.attributes.add') }
];
const toast = useToastStore();

async function handleSubmit() {
    if (!validate()) {
        toast.error(t('common.errors.form_invalid'));

        return;
    }

    isLoading.value = true;
    try {
        const { data } = await adminApiClient.post<AttributeCreateResponse>(CATALOG_API_ENDPOINTS.ATTRIBUTES.CREATE, form);
        toast.persist(data.message);
        window.location.href = ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST;
    } catch (e: unknown) {
        const err = e as ApiError;
        err.violations?.forEach(v => {
            violations[v.field] = v.message;
        });
    } finally {
        isLoading.value = false;
    }
}
</script>
