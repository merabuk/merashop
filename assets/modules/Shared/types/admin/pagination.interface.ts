export interface PaginationMeta {
    nextCursor: string | null;
    totalCount: number;
    perPage: number;
    unit: string;
}

export interface PaginatedCollection<T> {
    items: T[];
    meta: PaginationMeta;
}
