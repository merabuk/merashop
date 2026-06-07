<template>
    <form @submit.prevent="emit('submit')" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <BaseInput
                    v-model="form.slug"
                    :label="t('common.slug')"
                    :error="violations.slug"
                    :maxLength="CATEGORY_CONSTRAINTS.SLUG.MAX_LENGTH"
                    :allowedPattern="CATEGORY_CONSTRAINTS.SLUG.ALLOWED_CHARS_REGEX"
                    placeholder="e.g. electronics"
                />
                <BaseSelect
                    v-model="form.status"
                    :label="t('common.status')"
                    :error="violations.status"
                >
                    <option v-for="status in statuses" :key="status" :value="status">
                        {{ t(`catalog.common.status.${status}`) }}
                    </option>
                </BaseSelect>
            </div>

            <div class="border-t pt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ t('catalog.common.translation_title') }}</h3>
                </div>

                <div class="flex border-b border-gray-200 mb-6">
                    <button
                        v-for="loc in SUPPORTED_LOCALES"
                        :key="loc.code"
                        type="button"
                        @click="activeLocale = loc.code"
                        class="px-6 py-3 border-b-2 font-medium text-sm transition-all flex items-center gap-2"
                        :class="[
                            activeLocale === loc.code
                                ? 'border-merashop-500 text-merashop-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            hasErrorInLocale(loc.code) ? 'text-red-600' : ''
                        ]"
                    >
                        <span>{{ loc.flag }}</span>
                        {{ loc.name }}
                        <span v-if="hasErrorInLocale(loc.code)" class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    </button>
                </div>

                <div v-for="loc in SUPPORTED_LOCALES" :key="loc.code">
                    <div v-show="activeLocale === loc.code" class="space-y-6 animate-in fade-in duration-300">
                        <BaseInput
                            v-model="form.translations[loc.code].name"
                            :label="t('catalog.common.name')"
                            :error="violations[`translations[${loc.code}].name`]"
                            :maxLength="CATEGORY_CONSTRAINTS.NAME.MAX_LENGTH"
                            :placeholder="getPlaceholder(t, 'catalog.common.name', loc.code)"
                        />

                        <BaseTextarea
                            v-model="form.translations[loc.code].description"
                            :label="t('catalog.common.description')"
                            :error="violations[`translations[${loc.code}].description`]"
                            :maxLength="CATEGORY_CONSTRAINTS.DESCRIPTION.MAX_LENGTH"
                            :placeholder="getPlaceholder(t, 'catalog.common.description', loc.code)"
                            :rows="5"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-8 py-4 flex justify-between items-center">
            <a :href="cancelHref" class="text-gray-500 hover:text-gray-700 font-medium">{{ t('common.cancel') }}</a>
            <BaseButton type="submit" :isLoading="isLoading">
                {{ isUpdate ? t('common.save_changes') : t('common.save') }}
            </BaseButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { getPlaceholder } from '@shared/services/inputService';
import BaseInput from '@shared/components/admin/UI/BaseInput.vue';
import BaseTextarea from '@shared/components/admin/UI/BaseTextarea.vue';
import BaseButton from '@shared/components/admin/UI/BaseButton.vue';
import BaseSelect from '@shared/components/admin/UI/BaseSelect.vue';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { CategoryForm } from "@catalog/composables/admin/useCategoryForm";
import { CATEGORY_CONSTRAINTS } from "@catalog/types/admin/category.constants";
import { CategoryStatus } from "@catalog/types/category.enum";

const form = defineModel<CategoryForm>('form', { required: true });
const props = defineProps<{
    violations: Record<string, string>,
    isLoading: boolean,
    isUpdate?: boolean,
    cancelHref: string,
}>();

const { t } = useI18n();
const emit = defineEmits(['submit']);

const activeLocale = ref(SUPPORTED_LOCALES[0].code);

const statuses = computed(() => Object.values(CategoryStatus));

const hasErrorInLocale = (localeCode: string): boolean => {
    return Object.keys(props.violations).some(key =>
        key.startsWith(`translations[${localeCode}]`)
    );
};
</script>
