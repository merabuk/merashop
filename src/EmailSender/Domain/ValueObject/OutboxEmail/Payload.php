<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use JsonException;
use Stringable;

final readonly class Payload implements EquatableInterface, Stringable
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
     * @throws JsonException
     */
    protected function getPrimitiveValue(): string
    {
        $data = $this->data;
        ksort($data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
