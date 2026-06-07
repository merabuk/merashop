import { reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { CategoryTranslation } from "@catalog/types/admin/category.interface";
import { CategoryStatus } from "@catalog/types/category.enum";
import { createCategorySchema } from "@catalog/validators/admin/category-validator";

export interface CategoryForm {
    parentId?: string;
    slug: string;
    status: CategoryStatus;
    translations: Record<string, CategoryTranslation>;
    version: number;
    sortOrder: number;
}

export function useCategoryForm() {
    const { t } = useI18n();
    const violations = reactive<Record<string, string>>({});
    const categorySchema = createCategorySchema(t);

    const initialTranslations = () => SUPPORTED_LOCALES.reduce((acc, loc) => {
        acc[loc.code] = {
            name: '',
            description: '',
        };
        return acc;
    }, {} as Record<string, CategoryTranslation>);

    const form: CategoryForm = reactive({
        parentId: null,
        slug: '',
        status: CategoryStatus.Active,
        translations: initialTranslations(),
        version: 1,
        sortOrder: 0,
    });

    const validate = () => {
        Object.keys(violations).forEach(k => delete violations[k]);
        const result = categorySchema.safeParse(form);

        if (!result.success) {
            result.error.issues.forEach((issue) => {
                const path = issue.path;
                let key: string;

                if (path[0] === 'translations') {
                    // TODO: check work with description
                    key = `translations[${String(path[1])}].name`;
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
        validate
    };
}
