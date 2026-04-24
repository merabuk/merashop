<?php

declare(strict_types=1);

use App\Shared\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::UnexpectedError->value => 'Виникла неочікувана помилка. Спробуйте пізніше',
    ErrorCodeEnum::ValidationFailed->value => 'Перевірка даних не вдалася. Перевірте правильність заповнення полів',
    ErrorCodeEnum::AccessDenied->value => 'Недостатньо прав для виконання цієї дії',
    ErrorCodeEnum::Unauthorized->value => 'Ви не авторизовані (термін дії сесії вичерпано або токен недійсний)',
    ErrorCodeEnum::ConcurrencyError->value => 'Запис у розділі "{entityName}" вже був змінений іншим користувачем. Будь ласка, оновіть сторінку',
    ErrorCodeEnum::InvalidRequestHeaderValue->value => 'Некоректне значення заголовка "{headerName}"',
];
