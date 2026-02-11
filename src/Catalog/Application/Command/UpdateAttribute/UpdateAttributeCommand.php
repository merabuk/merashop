<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Shared\Application\Command\CommandInterface;

final readonly class UpdateAttributeCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $code,
        public string $type,
        /**
         * @var array<string, array{name: string}>
         */
        public array $translations,
    ) {
    }
}
