export default {
    catalog: {
        title: 'Каталог',
        common: {
            name: 'Назва',
            code: 'Технічний код',
            type: 'Тип даних',
            actions: 'Дії',
            is_active: 'Активний(а)',
            value: 'Значення'
        },
        attributes: {
            title: 'Атрибути товарів',
            description: 'Керування характеристиками товарів вашого магазину',
            add: 'Створити атрибут',
            empty_list: 'Атрибутів ще не створено',
            empty_list_alter: 'Додайте свій перший атрибут, щоб почати роботу.',
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
            }
        },
        options: {
            title: 'Опції атрибута',
            add: 'Додати опцію',
            code: 'Код опції',
            base_ratio: 'Коефіцієнт'
        },
        categories: {
            title: 'Категорії товарів',
            add: 'Додати категорію',
            empty_list: 'Список категорій порожній',
            table: {
                name: 'Назва',
                slug: 'Slug',
                actions: 'Дії'
            }
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
