export default {
    common: {
        login: 'Увійти',
        logging : 'Вхід...',
        logout: 'Вийти',
        save: 'Зберегти',
        saving: 'Збереження...',
        cancel: 'Скасувати',
        dashboard: 'Панель керування',
        management: 'Управління',
        catalog: 'Каталог',
        customers: 'Клієнти',
        errors: {
            fail_load: 'Помилка завантаження',
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
