export interface ApiError {
    errorCode: string;
    message: string;
    violations?: Array<{ field: string; message: string }>;
}
