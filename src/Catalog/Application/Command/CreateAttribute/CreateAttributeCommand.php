<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateAttributeCommand implements CommandInterface
{
    public function __construct(
        public string $code,
        public string $type,
        /**
         * @var array<string, array{name: string}>
         */
        public array $translations,
        public string $adminUlid,
    ) {
    }
}
