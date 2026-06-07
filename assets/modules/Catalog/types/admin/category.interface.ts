export interface CategoryInterface {
    ulid: string;
    name: string;
    slug: string;
}

export interface CategoryCreateInterface {
    message: string;
}

export interface CategoryTranslation {
    name: string;
    description?: string;
}
