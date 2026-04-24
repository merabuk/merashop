export const ERROR_CODES = {
    UNEXPECTED_ERROR: 'UnexpectedError',
} as const;

export interface ApiError {
    errorCode: string;
    message: string;
    violations?: Array<{ field: string; message: string }>;
}
