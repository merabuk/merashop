<template>
    <form @submit.prevent="emit('submit')" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <BaseInput
                    v-model="form.code"
                    :label="t('catalog.common.code')"
                    :error="violations.code"
                    :maxLength="ATTRIBUTE_CONSTRAINTS.CODE.MAX_LENGTH"
                    :allowedPattern="ATTRIBUTE_CONSTRAINTS.CODE.ALLOWED_CHARS_REGEX"
                    placeholder="e.g. color-code"
                />
                <BaseSelect
                    v-model="form.type"
                    :label="t('catalog.common.type')"
                    :error="violations.type"
                    :disabled="isUpdate && !isTypeChangeAllowed"
                    :class="isUpdate && !isTypeChangeAllowed ? 'opacity-60 cursor-not-allowed' : '' "
                    :title="isUpdate && !isTypeChangeAllowed ? t('catalog.attributes.type_change_not_allowed') : '' "
                >
                    <option v-for="type in types" :key="type" :value="type">
                        {{ t(`catalog.attributes.types.${type}`) }}
                    </option>
                </BaseSelect>
            </div>

            <div class="border-t pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('catalog.common.translation_title') }}</h3>
                <div class="space-y-4">
                    <div v-for="loc in SUPPORTED_LOCALES" :key="loc.code" class="flex items-center gap-4">
                        <span class="text-2xl mt-8">{{ loc.flag }}</span>
                        <div class="flex-1">
                            <BaseInput
                                v-model="form.translations[loc.code].name"
                                :label="`${t('catalog.common.name')} (${loc.name})`"
                                :error="violations[`translations[${loc.code}].name`]"
                                :maxLength="ATTRIBUTE_CONSTRAINTS.NAME.MAX_LENGTH"
                                :placeholder="getPlaceholder(t, 'catalog.common.name', loc.code)"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="needsOptions" class="border-t pt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ t('catalog.options.title') }}</h3>
                    <BaseButton type="button"
                                variant="secondary"
                                @click="emit('addOption')">
                        + {{ t('catalog.options.add') }}
                    </BaseButton>
                </div>
                <div v-if="violations.options" class="mb-4 text-red-500 text-sm italic">{{ violations.options }}</div>

                <div class="space-y-4">
                    <div v-for="(option, index) in form.options" :key="index"
                         class="p-6 border-2 border-gray-100 rounded-xl bg-white shadow-sm relative group mb-4 transition-all hover:border-merashop-200">

                        <button type="button" @click="emit('removeOption', index)"
                                class="absolute -top-3 -right-3 bg-white border shadow-sm text-gray-400 hover:text-red-500 w-8 h-8 rounded-full flex items-center justify-center transition-all">
                            ✕
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <BaseInput
                                v-model="option.code"
                                :label="t('catalog.options.code')"
                                :error="violations[`options[${index}].code`]"
                                :maxLength="OPTION_CONSTRAINTS.CODE.MAX_LENGTH"
                                :allowedPattern="OPTION_CONSTRAINTS.CODE.ALLOWED_CHARS_REGEX"
                                placeholder="option-code"
                            />

                            <div v-if="form.type === AttributeType.Dimension">
                                <BaseNumberInput
                                    v-model.number="option.baseRatio"
                                    :label="t('catalog.options.base_ratio')"
                                    :error="violations[`options[${index}].baseRatio`]"
                                    step="0.0001"
                                >
                                </BaseNumberInput>
                            </div>

                            <div class="flex items-center pt-6">
                                <label class="flex items-center cursor-pointer group/check">
                                    <input v-model="option.isActive" type="checkbox"
                                           class="w-5 h-5 text-merashop-500 border-gray-300 rounded focus:ring-merashop-500 transition">
                                    <span class="ml-3 text-sm text-gray-700 font-semibold">{{ t('catalog.common.is_active') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                            <div v-for="loc in SUPPORTED_LOCALES" :key="loc.code" class="flex-1">
                                <BaseInput
                                    v-model="option.translations[loc.code].value"
                                    :label="`${t('catalog.common.value')} (${loc.code})`"
                                    :error="violations[`options[${index}].translations[${loc.code}].value`]"
                                    :maxLength="OPTION_CONSTRAINTS.VALUE.MAX_LENGTH"
                                    :placeholder="getPlaceholder(t, 'catalog.common.value', loc.code)"
                                />
                            </div>
                        </div>
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
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { getPlaceholder } from '@shared/services/inputService';
import BaseInput from '@shared/components/admin/UI/BaseInput.vue';
import BaseButton from '@shared/components/admin/UI/BaseButton.vue';
import BaseNumberInput from '@shared/components/admin/UI/BaseNumberInput.vue';
import BaseSelect from '@shared/components/admin/UI/BaseSelect.vue';
import { AttributeForm } from '@catalog/composables/admin/useAttributeForm';
import { AttributeType } from '@catalog/types/attribute.enum';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { OPTION_CONSTRAINTS } from '@catalog/types/admin/option.constants';
import { ATTRIBUTE_CONSTRAINTS, ATTRIBUTE_TYPES_ALLOWED_TO_CHANGE } from '@catalog/types/admin/attribute.constants';

const form = defineModel<AttributeForm>('form', { required: true });
const props = defineProps<{
    violations: Record<string, string>,
    isLoading: boolean,
    isUpdate?: boolean,
    cancelHref: string,
    needsOptions?: boolean,
}>();

const { t } = useI18n();

const emit = defineEmits(['submit', 'addOption', 'removeOption']);

const isTypeChangeAllowed = computed(() => {
    if (props.isUpdate) {
        return ATTRIBUTE_TYPES_ALLOWED_TO_CHANGE(form.value.type).includes(form.value.type);
    }
    return true;
});

const types = computed(() => {
    if (props.isUpdate) {
        return isTypeChangeAllowed.value ? ATTRIBUTE_TYPES_ALLOWED_TO_CHANGE(form.value.type) : [form.value.type];
    }
    return Object.values(AttributeType);
});
</script>
