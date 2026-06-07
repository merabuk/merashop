<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto">
            <BaseBreadcrumbs :items="breadcrumbs" />

            <CategoryForm
                v-model:form="form"
                :violations="violations"
                :is-loading="isLoading"
                :is-update="false"
                :cancel-href="ADMIN_WEB_ENDPOINTS.CATEGORIES.LIST"
                @submit="handleSubmit"
            />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
import BaseBreadcrumbs from '@shared/components/admin/UI/BaseBreadcrumbs.vue';
import CategoryForm from "@catalog/components/admin/Category/CategoryForm.vue";
import { BreadcrumbItem } from '@shared/types/admin/breadcrumb.interface';
import { IconEnum } from '@shared/types/admin/icon.enum';
import { useCategoryForm } from "@catalog/composables/admin/useCategoryForm";
import adminApiClient from '@shared/api/adminApiClient';
import { CategoryCreateInterface } from "@catalog/types/admin/category.interface";
import { CATALOG_API_ENDPOINTS } from '@catalog/paths/admin/api';
import { ADMIN_WEB_ENDPOINTS } from '@shared/paths/admin/web';
import type { ApiError } from '@shared/types/error';
import { useToastStore } from '@shared/stores/admin/useToastStore';

const { t } = useI18n();
const { form, violations, validate } = useCategoryForm();
const isLoading = ref(false);
const breadcrumbs: BreadcrumbItem[] = [
    { label: t('catalog.title'), icon: IconEnum.Catalog },
    { label: t('catalog.categories.title'), href: ADMIN_WEB_ENDPOINTS.CATEGORIES.LIST, icon: IconEnum.Categories },
    { label: t('catalog.categories.add') }
];
const toast = useToastStore();

async function handleSubmit() {
    if (!validate()) {
        toast.error(t('common.errors.form_invalid'));

        return;
    }

    isLoading.value = true;
    try {
        const { data } = await adminApiClient.post<CategoryCreateInterface>(CATALOG_API_ENDPOINTS.CATEGORIES.CREATE, form);
        toast.persist(data.message);
        window.location.href = ADMIN_WEB_ENDPOINTS.CATEGORIES.LIST;
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
