<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObject;

use App\Users\Domain\Exception\InvalidUserIdException;
use Symfony\Component\Uid\Ulid;

final class UserId
{
    private string $id;

    /**
     * @throws InvalidUserIdException
     */
    private function __construct(string $id)
    {
        if (!Ulid::isValid($id)) {
            throw InvalidUserIdException::becauseItIsNotAValidUlid($id);
        }

        $this->id = $id;
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function generate(): self
    {
        return new self(Ulid::generate());
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
