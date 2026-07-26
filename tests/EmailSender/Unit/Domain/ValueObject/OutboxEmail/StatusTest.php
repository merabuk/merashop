<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailStatusException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class StatusTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('statusEnumProvider')]
    public function testItCreatesValidStatusFromEnum(StatusEnum $enum): void
    {
        $vo = Status::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('statusEnumProvider')]
    public function testItCreatesValidStatusFromString(StatusEnum $enum): void
    {
        $vo = Status::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function statusEnumProvider(): iterable
    {
        foreach (StatusEnum::cases() as $case) {
            yield $case->value => [$case];
        }
    }

    #[DataProvider('factoryMethodProvider')]
    public function testItCreatesCorrectStatusFromFactoryMethods(
        Status $vo,
        StatusEnum $expectedEnum,
        string $checkMethod,
    ): void {
        self::assertSame($expectedEnum, $vo->value());
        self::assertTrue($vo->$checkMethod());
    }

    public static function factoryMethodProvider(): iterable
    {
        yield 'created' => [Status::created(), StatusEnum::Created, 'isCreated'];
        yield 'processing' => [Status::processing(), StatusEnum::Processing, 'isProcessing'];
        yield 'sent' => [Status::sent(), StatusEnum::Sent, 'isSent'];
        yield 'failed' => [Status::failed(), StatusEnum::Failed, 'isFailed'];
        yield 'failedPermanently' => [Status::failedPermanently(), StatusEnum::FailedPermanently, 'isFailedPermanently'];
    }

    public function testItTrimsInput(): void
    {
        $status = StatusEnum::Sent;
        $vo = Status::fromString('  '.$status->value.'  ');
        self::assertTrue($vo->isSent());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Status::class,
            enum: StatusEnum::Created,
            anotherEnum: StatusEnum::Sent,
        );
    }

    #[DataProvider('invalidStatusProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailStatusException::class);
        Status::fromString($invalidValue);
    }

    public static function invalidStatusProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['CREATED'];
        yield 'random string' => ['not-a-status'];
    }
}
