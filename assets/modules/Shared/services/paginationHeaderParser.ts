import type { AxiosResponse } from 'axios';
import type { PaginationMeta } from '@shared/types/pagination.interface';

export function parsePaginationHeaders(response: AxiosResponse): PaginationMeta {
    const contentRange = response.headers['content-range'];
    const nextCursor = response.headers['x-next-cursor'] || null;

    let totalCount = 0;
    let perPage = 20;
    let unit = 'items';

    if (contentRange) {
        const [unitPart, rangePart] = contentRange.split(' ');
        const [range, total] = rangePart.split('/');
        const [start, end] = range.split('-');

        unit = unitPart;
        totalCount = parseInt(total, 10);
        perPage = parseInt(end, 10) - parseInt(start, 10);
    }

    return {
        nextCursor,
        totalCount,
        perPage,
        unit
    };
}
