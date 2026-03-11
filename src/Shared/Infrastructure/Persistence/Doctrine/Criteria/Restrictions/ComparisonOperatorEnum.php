<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions;

enum ComparisonOperatorEnum: string
{
    case Equal = '=';
    case NotEqual = '!=';
    case GreaterThan = '>';
    case LessThan = '<';
    case In = 'IN';
    case Like = 'LIKE';
}
