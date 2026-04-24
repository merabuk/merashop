import { UserType } from '@shared/types/user';

export interface JwtPayload {
    iss: string;
    jti: string;
    iat: number;
    exp: number;
    sub: string;
    roles: string[];
    sub_type: UserType;
    scopes: string[];
}

export interface SessionUser {
    identifier: string;
    roles: string[];
    type: UserType;
}

export interface AuthResponse {
    access_token: string;
    refresh_token: string;
    token_type: string;
    expires_in: number;
}
