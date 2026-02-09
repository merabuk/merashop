<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use App\Catalog\Domain\Exception\InvalidNameException;
use App\Shared\Domain\Exception\ValueObject\Translation\InvalidTranslationNameException;
use App\Shared\Domain\ValueObject\Translation\Name as BaseName;

final class Name extends BaseName
{
    public const int MAX_LENGTH = 255;

    /**
     * @throws InvalidNameException
     */
    public function __construct(string $value)
    {
        try {
            parent::__construct($value);
        } catch (InvalidTranslationNameException $e) {
            throw InvalidNameException::fromBaseException($e);
        }
    }

    /**
     * @throws InvalidNameException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
