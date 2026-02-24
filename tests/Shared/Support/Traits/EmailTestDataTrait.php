<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Service\EmailValidator;

trait EmailTestDataTrait
{
    protected static function createEmailWithLongLocalPart(): string
    {
        return str_repeat('a', EmailValidator::LOCAL_PART_MAX_LENGTH + 1).'@example.com';
    }

    protected static function createEmailExceedingLength(int $totalLimit): string
    {
        $localPart = str_repeat('l', EmailValidator::LOCAL_PART_MAX_LENGTH);
        $at = '@';
        $targetLength = $totalLimit + 1;

        $prefix = $localPart.$at;
        $neededDomainLength = $targetLength - mb_strlen($prefix);

        $domain = '';
        while (mb_strlen($domain) < $neededDomainLength) {
            $remaining = $neededDomainLength - mb_strlen($domain);

            if ($remaining > EmailValidator::DOMAIN_PART_MAX_LENGTH + 1) {
                $domain .= str_repeat('d', EmailValidator::DOMAIN_PART_MAX_LENGTH).'.';
            } else {
                if ($remaining >= 4) {
                    $domain .= str_repeat('d', $remaining - 4).'.com';
                } else {
                    $domain .= str_repeat('d', $remaining);
                }
            }
        }

        return $prefix.$domain;
    }
}
