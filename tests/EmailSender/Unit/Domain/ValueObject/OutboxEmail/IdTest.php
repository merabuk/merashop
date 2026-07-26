<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailIdException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
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
        $this->expectException(InvalidOutboxEmailIdException::class);
        Id::fromInt($invalidValue);
    }
}
