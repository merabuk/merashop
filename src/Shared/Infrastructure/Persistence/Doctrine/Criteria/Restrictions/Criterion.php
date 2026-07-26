<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use RuntimeException;

final readonly class Criterion
{
    public function __construct(
        public string $field,
        public mixed $value,
        public ComparisonOperatorEnum $operator = ComparisonOperatorEnum::Equal,
        private mixed $type = null,
    ) {
        $this->getType();
    }

    public function getType(): ArrayParameterType|ParameterType|int|string|null
    {
        return match (true) {
            is_null($this->type),
            is_string($this->type),
            is_integer($this->type),
            $this->type instanceof ParameterType,
            $this->type instanceof ArrayParameterType => $this->type,
            default => throw new RuntimeException(sprintf('Not supported type: %s', get_debug_type($this->type))),
        };
    }
}
