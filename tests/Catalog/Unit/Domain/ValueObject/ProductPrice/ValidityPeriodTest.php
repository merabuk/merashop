<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidityPeriodException;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ValidityPeriodTest extends TestCase
{
    #[DataProvider('validValidityPeriodProvider')]
    public function testItCreatesValidValidityPeriod(
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): void {
        $from = ValidFrom::fromDateTime($validFrom);
        $to = ValidTo::fromDateTime($validTo);
        $vo = new ValidityPeriod(from: $from, to: $to);

        self::assertSame($from, $vo->getFrom());
        self::assertSame($to, $vo->getTo());
    }

    #[DataProvider('validValidityPeriodProvider')]
    public function testItCreatesValidValidityPeriodFromDateTimeRange(
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): void {
        $vo = ValidityPeriod::fromDateTimeRange(from: $validFrom, to: $validTo);

        self::assertSame($validFrom, $vo->getFrom()->value());
        self::assertSame($validTo, $vo->getTo()->value());
    }

    #[DataProvider('validValidityPeriodProvider')]
    public function testItCreatesValidValidityPeriodFromStrings(
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): void {
        $format = 'Y-m-d H:i:s';
        $vo = ValidityPeriod::fromStrings(from: $validFrom->format($format), to: $validTo->format($format));

        $toStringFormat = DateTimeValueObject::OUTPUT_FORMAT;
        self::assertSame($validFrom->format($toStringFormat), (string) $vo->getFrom());
        self::assertSame($validTo->format($toStringFormat), (string) $vo->getTo());
    }

    public static function validValidityPeriodProvider(): iterable
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        yield 'simple' => [
            'validFrom' => $clock->now()->modify('-1 days'),
            'validTo' => $clock->now()->modify('+30 days'),
        ];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        $date1 = $clock->now()->modify('+1 days');
        $date2 = $clock->now()->modify('+30 days');
        $date3 = $clock->now()->modify('+31 days');

        $vo1 = ValidityPeriod::fromDateTimeRange(from: $date1, to: $date2);
        $vo2 = ValidityPeriod::fromDateTimeRange(from: $date1, to: $date2);
        $vo3 = ValidityPeriod::fromDateTimeRange(from: $date1, to: $date3);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItContainsDateInRange(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        $before = $clock->now()->modify('+1 days');
        $from = $clock->now()->modify('+2 days');
        $to = $clock->now()->modify('+30 days');
        $after = $clock->now()->modify('+31 days');

        $vo = ValidityPeriod::fromDateTimeRange(from: $from, to: $to);

        self::assertFalse($vo->contains($before));
        self::assertTrue($vo->contains($from));
        self::assertTrue($vo->contains($to));
        self::assertFalse($vo->contains($after));
    }

    #[DataProvider('invalidValidityPeriodProvider')]
    public function testThrowsExceptionOnInvalidInput(
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): void {
        $this->expectException(InvalidProductPriceValidityPeriodException::class);
        ValidityPeriod::fromDateTimeRange(from: $validFrom, to: $validTo);
    }

    public static function invalidValidityPeriodProvider(): iterable
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        yield 'validFromAfterValidTo' => [
            'validFrom' => $clock->now()->modify('+1 seconds'),
            'validTo' => $clock->now(),
        ];
        yield 'validFromEqualsValidTo' => [
            'validFrom' => $clock->now(),
            'validTo' => $clock->now(),
        ];
    }
}
