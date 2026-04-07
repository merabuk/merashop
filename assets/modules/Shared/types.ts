export interface ApiError {
    errorCode: string;
    message: string;
    violations?: Array<{ property: string; message: string }>;
}
