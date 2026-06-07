export default {
    common: {
        actions: 'Actions',
        loading: 'Loading...',
        login: 'Login',
        logging : 'Logging...',
        logout: 'Logout',
        save: 'Save',
        save_changes: 'Save changes',
        saving: 'Saving...',
        cancel: 'Cancel',
        dashboard: 'Dashboard',
        management: 'Management',
        catalog: 'Catalog',
        customers: 'Customers',
        slug: 'Slug',
        status: 'Status',
        errors: {
            session_expired: 'Session expired. Please log in again',
            access_denied: 'Access denied. You do not have permission to perform this action',
            server_error: 'An unexpected error occurred. Please try again later',
            fail_load: 'Loading error',
            fail_load_entity_data: 'Failed to load entity data',
            ulid_not_found: 'ULID not found',
            required: 'Required field',
            format: 'Wrong format',
            positive: 'Must be greater than 0',
            form_invalid: 'Please fix form errors',
            min_length: 'Must be at least {min} characters long',
            max_length: 'Must be at most {max} characters long',
            invalid_chars: 'Invalid characters. Allowed: {allowed_chars}',
            not_starts_with: 'Must not start with "{chars}"',
            not_ends_with: 'Must not end with "{chars}"',
            not_multiple_in_row: 'Must not contain "{chars}" in a row {times} or more times',
        },
        pagination: {
            load_more: 'Load more',
            per_page: 'Per page',
        }
    },
    auth: {
        admin: {
            login_title: 'Login to admin panel',
            username: 'Username',
            password: 'Password'
        },
        shop: {
            login_title: 'Login',
            username: 'Username',
            password: 'Password'
        }
    }
};
