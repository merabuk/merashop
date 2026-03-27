<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Exception\Attribute\AttributeOptionUniqueException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeOptionItemException;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;

/**
 * @extends AbstractCollection<AttributeOption>
 */
final readonly class OptionCollection extends AbstractCollection
{
    private bool $initialized;

    /**
     * @param AttributeOption[] $items
     *
     * @throws InvalidAttributeOptionItemException
     * @throws AttributeOptionUniqueException
     */
    public function __construct(array $items, bool $initialized = true)
    {
        $this->initialized = $initialized;

        if ($this->initialized) {
            try {
                $this->ensureDataType($items);
                $this->ensureUnique($items);
            } catch (InvalidAbstractCollectionItemException $e) {
                throw InvalidAttributeOptionItemException::fromBase($e);
            }
        }

        parent::__construct($items);
    }

    /**
     * @throws AttributeOptionUniqueException
     * @throws InvalidAttributeOptionItemException
     */
    public static function uninitialized(): self
    {
        return new self([], false);
    }

    /**
     * @throws InvalidAttributeOptionItemException
     * @throws AttributeOptionUniqueException
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param AttributeOption[] $items
     *
     * @throws InvalidAttributeOptionItemException
     * @throws AttributeOptionUniqueException
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function getById(int|AttributeOptionId $id): ?AttributeOption
    {
        $idValue = $id instanceof AttributeOptionId ? $id->value() : $id;

        return array_find($this->items, fn (AttributeOption $item) => $item->getId()?->value() === $idValue);
    }

    public function getByUlid(string|AttributeOptionUlid $ulid): ?AttributeOption
    {
        $ulidValue = $ulid instanceof AttributeOptionUlid ? $ulid->value() : $ulid;

        return array_find($this->items, fn (AttributeOption $item) => $item->getUlid()->value() === $ulidValue);
    }

    protected function getExpectedClass(): string
    {
        return AttributeOption::class;
    }

    /**
     * @param AttributeOption[] $items
     *
     * @throws AttributeOptionUniqueException
     */
    private function ensureUnique(array $items): void
    {
        $codes = [];
        $ulids = [];
        foreach ($items as $option) {
            if (isset($codes[$option->getCode()->value()])) {
                throw AttributeOptionUniqueException::becauseDuplicateOption($option->getCode()->value(), 'code');
            }
            if (isset($ulids[$option->getUlid()->value()])) {
                throw AttributeOptionUniqueException::becauseDuplicateOption($option->getUlid()->value(), 'ulid');
            }
            $codes[$option->getCode()->value()] = true;
            $ulids[$option->getUlid()->value()] = true;
        }
    }
}
