import { IconEnum } from "@shared/types/admin/icon.enum";

export interface BreadcrumbItem {
    label: string;
    href?: string;
    icon?: IconEnum;
}
