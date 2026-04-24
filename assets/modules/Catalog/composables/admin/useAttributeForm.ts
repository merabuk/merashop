import { reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { AttributeType } from '@catalog/types/attribute.enum';
import { ATTRIBUTE_TYPES_HAS_OPTIONS } from '@catalog/types/admin/attribute.constants';
import { createAttributeSchema } from '@catalog/validators/admin/attribute-validator';
import type { AttributeOption, OptionTranslation } from '@catalog/types/admin/option.interface';
import type { AttributeTranslation } from '@catalog/types/admin/attribute.interface';

export interface AttributeForm {
    code: string;
    type: AttributeType;
    translations: Record<string, AttributeTranslation>;
    options: AttributeOption[];
    version: number;
}

export function useAttributeForm() {
    const { t } = useI18n();
    const violations = reactive<Record<string, string>>({});
    const attributeSchema = createAttributeSchema(t);

    const initialTranslations = () => SUPPORTED_LOCALES.reduce((acc, loc) => {
        acc[loc.code] = { name: '' };
        return acc;
    }, {} as Record<string, AttributeTranslation>);

    const form: AttributeForm = reactive({
        code: '',
        type: AttributeType.String,
        translations: initialTranslations(),
        options: [] as AttributeOption[],
        version: 1
    });

    const needsOptions = computed(() => ATTRIBUTE_TYPES_HAS_OPTIONS.includes(form.type as AttributeType));

    const addOption = () => {
        form.options.push({
            ulid: null,
            code: '',
            isActive: true,
            baseRatio: 1,
            translations: SUPPORTED_LOCALES.reduce((acc, loc) => ({
                ...acc,
                [loc.code]: { value: '' }
            }), {} as Record<string, OptionTranslation>)
        });
    };

    const removeOption = (index: number) => form.options.splice(index, 1);

    const validate = () => {
        Object.keys(violations).forEach(k => delete violations[k]);
        const result = attributeSchema.safeParse(form);

        if (!result.success) {
            result.error.issues.forEach((issue) => {
                const path = issue.path;
                let key: string;

                if (path[0] === 'translations') {
                    key = `translations[${String(path[1])}].name`;
                } else if (path[0] === 'options') {
                    if (path[2] === 'translations') {
                        key = `options[${String(path[1])}].translations[${String(path[3])}].value`;
                    } else {
                        key = `options[${String(path[1])}].${String(path[2])}`;
                    }
                } else {
                    key = path.join('.');
                }
                violations[key] = issue.message;
            });

            return false;
        }
        return true;
    };

    return {
        form,
        violations,
        needsOptions,
        addOption,
        removeOption,
        validate
    };
}
