<template>
    <AdminLayout>
        <div class="max-w-3xl mx-auto">
            <nav class="flex mb-6 text-sm">
                <a href="/admin/catalog/attributes" class="text-gray-500 hover:text-merashop-500 transition">
                    {{ $t('catalog.attributes.title') }}
                </a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900 font-medium">{{ $t('catalog.attributes.add') }}</span>
            </nav>

            <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput
                            v-model="form.code"
                            :label="$t('catalog.common.code')"
                            :error="violations.code"
                            :maxLength="ATTRIBUTE_CONSTRAINTS.CODE.MAX_LENGTH"
                            :allowedPattern="ATTRIBUTE_CONSTRAINTS.CODE.ALLOWED_CHARS_REGEX"
                            placeholder="e.g. color-code"
                        />
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">{{ $t('catalog.common.type') }}</label>
                            <select v-model="form.type"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-merashop-500 outline-none bg-white transition">
                                <option v-for="type in types" :key="type" :value="type">
                                    {{ $t(`catalog.attributes.types.${type}`) }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t pt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('catalog.attributes.translation_title') }}</h3>
                        <div class="space-y-4">
                            <div v-for="loc in SUPPORTED_LOCALES" :key="loc.code" class="flex items-center gap-4">
                                <span class="text-2xl mt-8" :title="loc.name">{{ loc.flag }}</span>
                                <div class="flex-1">
                                    <BaseInput
                                        v-model="form.translations[loc.code].name"
                                        :label="`${$t('catalog.common.name')} (${loc.name})`"
                                        :error="violations[`translations[${loc.code}].name`]"
                                        :maxLength="ATTRIBUTE_CONSTRAINTS.NAME.MAX_LENGTH"
                                        placeholder="e.g. Color"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="needsOptions" class="border-t pt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $t('catalog.options.title') }}</h3>
                            <button type="button" @click="addOption"
                                    class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md transition">
                                + {{ $t('catalog.options.add') }}
                            </button>
                        </div>

                        <div v-if="violations.options" class="mb-4 text-red-500 text-sm italic">{{ violations.options }}</div>

                        <div class="space-y-4">
                            <div v-for="(option, index) in form.options" :key="index"
                                 class="p-6 border-2 border-gray-100 rounded-xl bg-white shadow-sm relative group mb-4 transition-all hover:border-merashop-200">

                                <button type="button" @click="removeOption(index)"
                                        class="absolute -top-3 -right-3 bg-white border shadow-sm text-gray-400 hover:text-red-500 w-8 h-8 rounded-full flex items-center justify-center transition-all">
                                    ✕
                                </button>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <BaseInput
                                        v-model="option.code"
                                        :label="$t('catalog.options.code')"
                                        :error="violations[`options[${index}].code`]"
                                        :maxLength="OPTION_CONSTRAINTS.CODE.MAX_LENGTH"
                                        :allowedPattern="OPTION_CONSTRAINTS.CODE.ALLOWED_CHARS_REGEX"
                                    />

                                    <div v-if="form.type === AttributeType.Dimension">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">{{ $t('catalog.options.base_ratio') }}</label>
                                        <input v-model.number="option.baseRatio"
                                               type="number" step="0.0001"
                                               class="w-full px-4 py-2 border rounded-lg transition-all duration-200 bg-white shadow-xs outline-none focus:outline-none"
                                               :class="[violations[`options[${index}].baseRatio`]
                                                    ? 'border-red-500 focus:border-red-600 focus:ring-4 focus:ring-red-500/10'
                                                    : 'border-gray-300 focus:border-merashop-500 focus:ring-4 focus:ring-emerald-500/10'
                                                ]"
                                        />
                                        <p v-if="violations[`options[${index}].baseRatio`]" class="text-red-500 text-xs mt-1">
                                            {{ violations[`options[${index}].baseRatio`] }}
                                        </p>
                                    </div>

                                    <div class="flex items-center pt-6">
                                        <label class="flex items-center cursor-pointer group/check">
                                            <input v-model="option.isActive" type="checkbox"
                                                   class="w-5 h-5 text-merashop-500 border-gray-300 rounded focus:ring-merashop-500 transition">
                                            <span class="ml-3 text-sm text-gray-700 font-semibold">{{ $t('catalog.common.is_active') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                                    <div v-for="loc in SUPPORTED_LOCALES" :key="loc.code" class="flex-1">
                                        <BaseInput
                                            v-model="option.translations[loc.code].value"
                                            :label="`${$t('catalog.common.value')} (${loc.code})`"
                                            :error="violations[`options[${index}].translations[${loc.code}].value`]"
                                            :maxLength="OPTION_CONSTRAINTS.VALUE.MAX_LENGTH"
                                            size="sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-4 flex justify-between items-center">
                    <a :href="ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST" class="text-gray-500 hover:text-gray-700 font-medium transition">
                        {{ $t('common.cancel') }}
                    </a>
                    <button type="submit" :disabled="isLoading"
                            class="bg-merashop-500 hover:bg-emerald-600 text-white font-bold py-2 px-8 rounded-lg shadow-md transition-all active:scale-95 disabled:opacity-50">
                        {{ isLoading ? $t('common.saving') : $t('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import {computed, reactive, ref} from 'vue';
    import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
    import BaseInput from '@shared/components/UI/admin/BaseInput.vue';
    import { AttributeType } from '@catalog/types/attribute.enum';
    import { ATTRIBUTE_CONSTRAINTS, ATTRIBUTE_TYPES_HAS_OPTIONS } from '@catalog/types/admin/attribute.constants';
    import { OPTION_CONSTRAINTS } from '@catalog/types/admin/option.constants';
    import { SUPPORTED_LOCALES } from '@shared/types/locale';
    import adminApiClient from '@shared/admin-api-client';
    import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';
    import { ADMIN_WEB_ENDPOINTS } from "@shared/web/admin/endpoints";
    import type { ApiError } from '@shared/types/error';
    import { useToastStore } from '@shared/store/useToastStore';
    import { AttributeCreateResponse } from "@catalog/types/admin/attribute.interface";
    import { useI18n } from 'vue-i18n';
    import { createAttributeSchema } from '@catalog/api/admin/attribute-validator';

    const toast = useToastStore();
    const isLoading = ref(false);
    const violations = reactive<Record<string, string>>({});
    const types = Object.values(AttributeType);
    const { t } = useI18n();
    const attributeSchema = createAttributeSchema(t);

    const needsOptions = computed(() =>
        ATTRIBUTE_TYPES_HAS_OPTIONS.includes(form.type as AttributeType)
    );

    interface AttributeTranslation {
        name: string;
    }

    interface OptionTranslation {
        value: string;
    }

    interface AttributeOption {
        code: string;
        isActive: boolean;
        baseRatio: number;
        translations: Record<string, OptionTranslation>;
    }

    const initialAttributeTranslations = SUPPORTED_LOCALES.reduce((acc, loc) => {
        acc[loc.code] = { name: '' };

        return acc;
    }, {} as Record<string, AttributeTranslation>);

    const form = reactive({
        code: '',
        type: types[0],
        translations: initialAttributeTranslations,
        options: [] as AttributeOption[]
    });

    function addOption() {
        const newOption: AttributeOption = {
            code: '',
            isActive: true,
            baseRatio: 1,
            translations: SUPPORTED_LOCALES.reduce((acc, loc) => ({
                ...acc,
                [loc.code]: { value: '' }
            }), {} as Record<string, OptionTranslation>)
        };

        form.options.push(newOption);
    }

    function removeOption(index: number) {
        form.options.splice(index, 1);
    }

    async function handleSubmit() {
        Object.keys(violations).forEach(k => delete violations[k]);

        const result = attributeSchema.safeParse(form);

        if (!result.success) {
            result.error.issues.forEach((issue) => {
                if (issue.path[0] === 'translations') {
                    const lang = String(issue.path[1]);
                    violations[`translations[${lang}].name`] = issue.message;

                    return;
                }

                if (issue.path[0] === 'options') {
                    const index = String(issue.path[1]);
                    const field = String(issue.path[2]);

                    if (field === 'translations') {
                        const lang = String(issue.path[3]);
                        violations[`options[${index}].translations[${lang}].value`] = issue.message;
                    } else {
                        violations[`options[${index}].${field}`] = issue.message;
                    }

                    return;
                }

                const flatPath = issue.path.join('.');
                violations[flatPath] = issue.message;
            });
            toast.error(t('common.errors.form_invalid'));

            return;
        }

        isLoading.value = true;
        try {
            const { data } = await adminApiClient.post<AttributeCreateResponse>(CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.CREATE, form);
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
