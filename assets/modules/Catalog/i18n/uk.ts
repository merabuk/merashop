export default {
    catalog: {
        title: 'Каталог',
        common: {
            name: 'Назва',
            code: 'Технічний код',
            type: 'Тип даних',
            actions: 'Дії',
            is_active: 'Активний(а)',
            value: 'Значення',
            color: 'Колір',
        },
        attributes: {
            title: 'Атрибути товарів',
            description: 'Керування характеристиками товарів вашого магазину',
            add: 'Створити атрибут',
            edit: 'Редагувати атрибут',
            empty_list: 'Атрибутів ще не створено',
            empty_list_alter: 'Додайте свій перший атрибут, щоб почати роботу',
            translation_title: 'Переклади',
            types: {
                string: 'Рядок',
                text: 'Текст',
                integer: 'Ціле число',
                float: 'Дробове число',
                boolean: 'Так/Ні',
                select: 'Вибір зі списку',
                multiselect: 'Множинний вибір',
                color: 'Колір',
                date: 'Дата',
                url: 'Посилання',
                dimension: 'Розмір/Габарити',
                image: 'Зображення'
            },
            type_change_not_allowed: 'Зміна типу атрибуту заборонена'
        },
        options: {
            title: 'Опції атрибута',
            add: 'Додати опцію',
            code: 'Код опції',
            base_ratio: 'Коефіцієнт'
        },
        categories: {
            title: 'Категорії товарів',
            description: 'Керуйте категоріями товарів вашого магазину',
            add: 'Додати категорію',
            empty_list: 'Список категорій порожній',
            empty_list_alter: 'Додайте свою першу категорію, щоб почати роботу',
        },
        products: {
            title: 'Продукти',
            add: 'Додати продукт',
            empty_list: 'Список продуктів порожній',
            table: {
                name: 'Назва',
                description: 'Опис',
                actions: 'Дії'
            }
        }
    }
};
