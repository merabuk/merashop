<?php

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use BackedEnum;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;

trait ValueObjectEqualityCheckTrait
{
    protected function assertIntegerVOProvidesEqualityCheck(string $className, int $value, int $anotherValue): void
    {
        $this->assertHasStaticMethod($className, 'fromInt');

        $vo1 = $className::fromInt($value);
        $vo2 = $className::fromInt($value);
        $vo3 = $className::fromInt($anotherValue);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertStringVOProvidesEqualityCheck(string $className, string $value, string $anotherValue): void
    {
        $this->assertHasStaticMethod($className, 'fromString');

        $vo1 = $className::fromString($value);
        $vo2 = $className::fromString($value);
        $vo3 = $className::fromString($anotherValue);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertBooleanVOProvidesEqualityCheck(string $className, bool $value, bool $anotherValue): void
    {
        $this->assertHasStaticMethod($className, 'fromBool');

        $vo1 = $className::fromBool($value);
        $vo2 = $className::fromBool($value);
        $vo3 = $className::fromBool($anotherValue);

        $this->assertVoProvidesEqualityCheck($vo1);
        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertArrayVOProvidesEqualityCheck(
        string $className,
        array $value,
        array $shuffledValue,
        array $anotherValue,
    ): void {
        $this->assertHasStaticMethod($className, 'fromArray');

        $vo1 = $className::fromArray($value);
        $vo2 = $className::fromArray($shuffledValue);
        $vo3 = $className::fromArray($anotherValue);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertDateTimeVOProvidesEqualityCheck(
        string $className,
        string|DateTimeImmutable $value,
        string|DateTimeImmutable $anotherValue,
    ): void {
        $data = is_string($value) ? new DateTimeImmutable($value) : $value;
        $anotherData = is_string($anotherValue) ? new DateTimeImmutable($anotherValue) : $anotherValue;
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $vo1 = $className::fromDateTime($data);
        $vo2 = $className::fromDateTime($data);
        $vo3 = $className::fromDateTime($anotherData);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertEnumVOProvidesEqualityCheck(string $className, BackedEnum $enum, BackedEnum $anotherEnum): void
    {
        $this->assertHasStaticMethod($className, 'fromEnum');

        $vo1 = $className::fromEnum($enum);
        $vo2 = $className::fromEnum($enum);
        $vo3 = $className::fromEnum($anotherEnum);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertStringCollectionVOProvidesEqualityCheck(
        string $className,
        array $values,
        array $shuffledValues,
        array $anotherValues,
    ): void {
        $this->assertHasStaticMethod($className, 'fromStrings');

        $vo1 = $className::fromStrings($values);
        $vo2 = $className::fromStrings($shuffledValues);
        $vo3 = $className::fromStrings($anotherValues);

        $this->assertVoProvidesEqualityCheck($vo1);

        $this->baseEqualityCheckAssertion($vo1, $vo2, $vo3);
    }

    protected function assertVoProvidesEqualityCheck(object $vo): void
    {
        Assert::assertInstanceOf(EquatableInterface::class, $vo);
    }

    protected function assertHasStaticMethod(string $className, string $methodName): void
    {
        Assert::assertTrue(
            condition: method_exists($className, $methodName),
            message: sprintf('%s class must implement %s method', $className, $methodName)
        );
    }

    private function baseEqualityCheckAssertion(
        EquatableInterface $same1,
        EquatableInterface $same2,
        EquatableInterface $other,
    ): void {
        Assert::assertTrue($same1->equals($same2));
        Assert::assertFalse($same1->equals($other));
    }
}
