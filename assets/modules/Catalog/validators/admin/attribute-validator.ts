import { z } from 'zod';
import { AttributeType } from '@catalog/types/attribute.enum';
import { ATTRIBUTE_CONSTRAINTS, ATTRIBUTE_TYPES_HAS_OPTIONS } from '@catalog/types/admin/attribute.constants';
import { OPTION_CONSTRAINTS } from '@catalog/types/admin/option.constants';

type TranslateFn = (key: string, named?: Record<string, unknown>) => string;

export const createAttributeSchema = (t: TranslateFn) => {
    const { CODE, NAME } = ATTRIBUTE_CONSTRAINTS;
    const { CODE: OPTION_CODE, VALUE: OPTION_NAME } = OPTION_CONSTRAINTS;

    const translationSchema = z.object({
        name: z.string()
            .min(NAME.MIN_LENGTH, t('common.errors.required'))
            .max(NAME.MAX_LENGTH, t('common.errors.max_length', { max: NAME.MAX_LENGTH }))
    });

    const optionSchema = z.object({
        code: z.string()
            .min(OPTION_CODE.MIN_LENGTH, t('common.errors.required'))
            .max(OPTION_CODE.MAX_LENGTH, t('common.errors.max_length', { max: OPTION_CODE.MAX_LENGTH }))
            .regex(OPTION_CODE.ALLOWED_CHARS_REGEX, t('common.errors.invalid_chars', { allowed_chars: 'a-z,0-9,-' }))
            .refine(val => !val.startsWith('-'), t('common.errors.not_starts_with', { chars: '-' }))
            .refine(val => !val.endsWith('-'), t('common.errors.not_ends_with', { chars: '-'}))
            .refine(val => !val.includes('--'), t('common.errors.not_multiple_in_row', { chars: '-', times: 2 })),
        isActive: z.boolean(),
        baseRatio: z.number()
            .positive(t('common.errors.positive'))
            .optional(),
        translations: z.record(
            z.string(),
            z.object({
                value: z.string()
                    .min(OPTION_NAME.MIN_LENGTH, t('common.errors.required'))
                    .max(OPTION_NAME.MAX_LENGTH, t('common.errors.max_length', { max: OPTION_NAME.MAX_LENGTH })),
            })
        )
    });

    return z.object({
        code: z.string()
            .min(CODE.MIN_LENGTH, t('common.errors.required'))
            .max(CODE.MAX_LENGTH, t('common.errors.max_length', { max: CODE.MAX_LENGTH }))
            .regex(CODE.ALLOWED_CHARS_REGEX, t('common.errors.invalid_chars', { allowed_chars: 'a-z,0-9,-' }))
            .refine(val => !val.startsWith('-'), t('common.errors.not_starts_with', { chars: '-' }))
            .refine(val => !val.endsWith('-'), t('common.errors.not_ends_with', { chars: '-'}))
            .refine(val => !val.includes('--'), t('common.errors.not_multiple_in_row', { chars: '-', times: 2 })),
        type: z.enum(AttributeType),
        translations: z.record(z.string(), translationSchema).refine((data) => {
            const values = Object.values(data) as Array<{ name: string }>;

            return values.some((trans) => trans.name.trim().length > 0);
        }, {
            message: t('common.errors.required')
        }),
        options: z.array(optionSchema)
            .optional()
    }).refine((data) => {
        const needsOptions = ATTRIBUTE_TYPES_HAS_OPTIONS.includes(data.type as AttributeType);
        return !(needsOptions && (!data.options || data.options.length === 0));
    }, {
        message: t('common.errors.required'),
        path: ['options']
    }).refine((data) => {
        if (data.type === AttributeType.Dimension && data.options) {
            return data.options.every(opt => typeof opt.baseRatio === 'number' && opt.baseRatio > 0);
        }
        return true;
    }, {
        message: t('common.errors.required'),
        path: ['options']
    });
};
