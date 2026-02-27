<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use DateTimeImmutable;
use Error;
use PHPUnit\Framework\Assert;
use ReflectionProperty;

trait EntityTechnicalMetadataTrait
{
    protected function assertHasCreatedAt(object $ormEntity): void
    {
        $createdAt = $this->getPropertyValue($ormEntity, 'createdAt');

        Assert::assertNotNull($createdAt, 'Gedmo Timestampable should set createdAt automatically');
        Assert::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        Assert::assertGreaterThan(
            new DateTimeImmutable('-10 seconds')->getTimestamp(),
            $createdAt->getTimestamp()
        );
    }

    protected function assertHasUpdatedAt(object $ormEntity): void
    {
        $updatedAt = $this->getPropertyValue($ormEntity, 'updatedAt');

        Assert::assertNotNull($updatedAt, 'Gedmo Timestampable should set updatedAt automatically');
        Assert::assertInstanceOf(DateTimeImmutable::class, $updatedAt);
    }

    private function getPropertyValue(object $object, string $propertyName): mixed
    {
        try {
            return $object->{$propertyName};
        } catch (Error) {
            $getter = 'get'.ucfirst($propertyName);

            if (method_exists($object, $getter)) {
                return $object->$getter();
            }

            $reflection = new ReflectionProperty($object, $propertyName);

            return $reflection->getValue($object);
        }
    }
}
