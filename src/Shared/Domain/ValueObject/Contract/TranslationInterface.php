<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

interface TranslationInterface
{
    /**
     * @return array<string, ?string>
     */
    public function toArray(): array;
}
