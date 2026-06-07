import { z } from 'zod';
import { CATEGORY_CONSTRAINTS } from "@catalog/types/admin/category.constants";

type TranslateFn = (key: string, named?: Record<string, unknown>) => string;

export const createCategorySchema = (t: TranslateFn) => {
    const { SLUG, NAME, DESCRIPTION } = CATEGORY_CONSTRAINTS;

    const translationSchema = z.object({
        name: z.string()
            .min(NAME.MIN_LENGTH, t('common.errors.required'))
            .max(NAME.MAX_LENGTH, t('common.errors.max_length', { max: NAME.MAX_LENGTH })),
        description: z.string()
            .min(DESCRIPTION.MIN_LENGTH, t('common.errors.required'))
            .max(DESCRIPTION.MAX_LENGTH, t('common.errors.max_length', { max: DESCRIPTION.MAX_LENGTH }))
            .optional(),
    });

    return z.object({
        slug: z.string()
            .min(SLUG.MIN_LENGTH, t('common.errors.required'))
            .max(SLUG.MAX_LENGTH, t('common.errors.max_length', { max: SLUG.MAX_LENGTH }))
            .regex(SLUG.ALLOWED_CHARS_REGEX, t('common.errors.invalid_chars', { allowed_chars: 'a-z,0-9,-' }))
            .refine(val => !val.startsWith('-'), t('common.errors.not_starts_with', { chars: '0-9,-' }))
            .refine(val => !val.endsWith('-'), t('common.errors.not_ends_with', { chars: '-'}))
            .refine(val => !val.includes('--'), t('common.errors.not_multiple_in_row', { chars: '-', times: 2 })),
        status: z.string(),
        translations: z.record(z.string(), translationSchema).refine((data) => {
            const values = Object.values(data) as Array<{ name: string, description?: string }>;

            return values.some((trans) => trans.name.trim().length > 0);
        }, {
            message: t('common.errors.required')
        }),
    });
};
