<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileIdException;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\IntegerIdTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class IdTest extends BaseUnitTest
{
    use IntegerIdTestTrait;

    public function testItCreatesValidId(): void
    {
        $this->assertValidIntegerId(Id::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertIdEquality(Id::class);
    }

    #[DataProvider('invalidIdProvider')]
    public function testThrowsExceptionOnInvalidInput(int $invalidValue): void
    {
        $this->expectException(InvalidCustomerProfileIdException::class);
        Id::fromInt($invalidValue);
    }
}
