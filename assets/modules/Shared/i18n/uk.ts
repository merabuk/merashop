export default {
    common: {
        loading: 'Завантаження...',
        login: 'Увійти',
        logging : 'Вхід...',
        logout: 'Вийти',
        save: 'Зберегти',
        save_changes: 'Зберегти зміни',
        saving: 'Збереження...',
        cancel: 'Скасувати',
        dashboard: 'Панель керування',
        management: 'Управління',
        catalog: 'Каталог',
        customers: 'Клієнти',
        slug: 'Slug',
        errors: {
            session_expired: 'Сесія закінчилася. Будь ласка, увійдіть знову',
            access_denied: 'Доступ заборонено. Ви не маєте прав для виконання цієї дії',
            server_error: 'Виникла непередбачувана помилка. Будь ласка, спробуйте пізніше',
            fail_load: 'Помилка завантаження',
            fail_load_entity_data: 'Не вдалося завантажити дані',
            ulid_not_found: 'ULID не знайдено',
            required: 'Це поле є обов’язковим',
            format: 'Невірний формат',
            positive: 'Має бути більше 0',
            form_invalid: 'Будь ласка, виправте помилки у формі',
            min_length: 'Має бути не менше {min} символів',
            max_length: 'Має бути не більше {max} символів',
            invalid_chars: 'Недопустимі символи. Дозволені: {allowed_chars}',
            not_starts_with: 'Не може починатися з "{chars}"',
            not_ends_with: 'Не може закінчуватися на "{chars}"',
            not_multiple_in_row: 'Не може містити "{chars}" в рядку {times} або більше разів підряд',
        },
        pagination: {
            load_more: 'Завантажити більше',
        }
    },
    auth: {
        admin: {
            login_title: 'Авторизація в панель керування',
            username: 'Логін',
            password: 'Пароль'
        },
        shop: {
            login_title: 'Авторизація',
            username: 'Логін',
            password: 'Пароль'
        }
    }
};
