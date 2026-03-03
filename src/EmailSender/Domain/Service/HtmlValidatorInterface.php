<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Service;

interface HtmlValidatorInterface
{
    public function isValid(string $html): bool;
}
