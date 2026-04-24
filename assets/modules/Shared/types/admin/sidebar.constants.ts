export enum AdminSidebarView {
    Dashboard = 'AdminDashboard',
    Attributes = 'AttributeListView',
    Categories = 'CategoryListView',
    Products = 'ProductListView',
    Customers = 'CustomerListView',
}

export const CATALOG_VIEWS: readonly string[] = [
    AdminSidebarView.Attributes,
    AdminSidebarView.Categories,
    AdminSidebarView.Products,
];
