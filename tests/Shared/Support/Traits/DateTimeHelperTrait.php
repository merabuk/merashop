<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use DateTime;
use DateTimeImmutable;

trait DateTimeHelperTrait
{
    protected function toDateTimeImmutable(DateTime|string $dateTime): DateTimeImmutable
    {
        if ($dateTime instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($dateTime);
        }

        return new DateTimeImmutable($dateTime);
    }
}
