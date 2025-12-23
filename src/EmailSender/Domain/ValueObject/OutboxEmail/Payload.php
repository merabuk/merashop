<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Payload implements \Stringable
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function value(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @throws \JsonException
     */
    protected function getPrimitiveValue(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
