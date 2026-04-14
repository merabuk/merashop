<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto">
            <BaseBreadcrumbs :items="breadcrumbs" />

            <div v-if="isInitialLoading" class="bg-white rounded-xl p-20 shadow-sm border text-center">
                <Spinner/>
            </div>

            <AttributeForm
                v-else
                v-model:form="form"
                :violations="violations"
                :is-loading="isLoading"
                :is-update="true"
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
import { ref, onMounted } from 'vue';
import { useI18n } from "vue-i18n";
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
import Spinner from '@shared/components/admin/UI/Spinner.vue';
import AttributeForm from '@catalog/components/admin/Attribute/AttributeForm.vue';
import BaseBreadcrumbs from "@shared/components/admin/UI/BaseBreadcrumbs.vue";
import { BreadcrumbItem } from "@shared/types/admin/breadcrumb.interface";
import { IconEnum } from "@shared/types/admin/icon.enum";
import { useAttributeForm } from '@catalog/composables/admin/useAttributeForm';
import { AttributeType } from '@catalog/types/attribute.enum';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { getTargetUlid } from "@shared/services/domDataProvider";
import adminApiClient from '@shared/adminApiClient';
import type { AttributeItemResponse, AttributeUpdateResponse } from "@catalog/types/admin/attribute.interface";
import type {
    AttributeOption,
    OptionTranslation,
    AttributeItemOptionItemResponse
} from "@catalog/types/admin/option.interface";
import type { ApiError } from "@shared/types/error";
import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';
import { ADMIN_WEB_ENDPOINTS } from "@shared/web/admin/endpoints";
import { useToastStore } from '@shared/store/useToastStore';

const targetUlid = getTargetUlid();
const { t } = useI18n();
const { form, violations, needsOptions, addOption, removeOption, validate } = useAttributeForm();
const isLoading = ref(false);
const isInitialLoading = ref(true);
const breadcrumbs: BreadcrumbItem[] = [
    { label: t('catalog.title'), icon: IconEnum.Catalog },
    { label: t('catalog.attributes.title'), href: ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST, icon: IconEnum.Attributes },
    { label: t('catalog.attributes.edit') }
];
const toast = useToastStore();

onMounted(async () => {
    if (!targetUlid) {
        toast.error(t('common.error.ulid_not_found'));

        return;
    }

    try {
        const { data } = await adminApiClient.get<AttributeItemResponse>(
            CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.GET(targetUlid)
        );

        form.code = data.code;
        form.type = data.type;
        form.version = data.version;

        SUPPORTED_LOCALES.forEach(loc => {
            form.translations[loc.code].name = data.translations[loc.code]?.name || '';
        });

        Object.values(data.options || {}).forEach((o: AttributeItemOptionItemResponse) => {
            const option: AttributeOption = {
                ulid: o.ulid,
                code: o.code,
                isActive: o.isActive,
                baseRatio: (
                    data.type === AttributeType.Dimension
                    && o.metadata
                    && o.metadata['baseRatio']
                ) ? parseFloat(o.metadata['baseRatio']) : null,
                translations: {} as Record<string, OptionTranslation>,
            };

            SUPPORTED_LOCALES.forEach(loc => {
                option.translations[loc.code] = {
                    value: o.translations[loc.code]?.value || ''
                };
            });

            form.options.push(option);
        })

    } catch {
        toast.error(t('common.error.fail_load_entity_data'));
    } finally {
        isInitialLoading.value = false;
    }
});

async function handleSubmit() {
    if (!targetUlid) {
        toast.error(t('common.error.ulid_not_found'));

        return;
    }

    if (!validate()) {
        toast.error(t('common.errors.form_invalid'));

        return;
    }

    isLoading.value = true;
    try {
        const { data } = await adminApiClient.put<AttributeUpdateResponse>(
            CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.UPDATE(targetUlid),
            form
        );
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
