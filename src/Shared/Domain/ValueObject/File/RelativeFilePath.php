<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\File;

use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class RelativeFilePath implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const string SEPARATOR = '/';
    public const int MAX_LENGTH = 511;
    public const array FORBIDDEN_NAMES = ['..'];

    private string $value;

    /**
     * @throws InvalidRelativePathException
     */
    public function __construct(string $path)
    {
        $path = mb_trim($path);

        $this->ensureIsValidPath($path);

        $this->value = $path;
    }

    /**
     * @throws InvalidRelativePathException
     */
    public static function fromString(?string $path): self
    {
        return new self((string) $path);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidRelativePathException
     */
    private function ensureIsValidPath(string $path): void
    {
        // TODO: Improve validation
        if (empty($path)) {
            throw InvalidRelativePathException::becauseItIsEmpty();
        }

        $separator = preg_quote(self::SEPARATOR, '/');

        if (preg_match(sprintf('/ |%s{2,}/', $separator), $path, $matches)) {
            throw InvalidRelativePathException::becauseItContainsInvalidCharacters();
        }

        if (preg_match(sprintf('/^%s|%s$/', $separator, $separator), $path, $matches)) {
            throw InvalidRelativePathException::becauseItHasInvalidFormat();
        }

        if (mb_strlen($path) > self::MAX_LENGTH) {
            throw InvalidRelativePathException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        $pathParts = explode(self::SEPARATOR, $path);
        $matches = array_filter($pathParts, fn (string $part) => in_array($part, self::FORBIDDEN_NAMES, true));

        if (!empty($matches)) {
            throw InvalidRelativePathException::becauseItContainsForbiddenNames();
        }
    }
}
