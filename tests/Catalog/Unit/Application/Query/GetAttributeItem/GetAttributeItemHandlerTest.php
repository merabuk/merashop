<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Query\GetAttributeItem;

use App\Catalog\Application\Query\GetAttributeItem\GetAttributeItemHandler;
use App\Catalog\Application\Query\GetAttributeItem\GetAttributeItemQuery;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetAttributeItemHandlerTest extends TestCase
{
    private AttributeReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $attribute = AttributeMother::createWithData();

        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->with(self::equalTo($attribute->getUlid()))
            ->willReturn($attribute);

        $query = $this->fillAndGetQuery(ulid: $attribute->getUlid()->value());

        $result = $this->createHandler()($query);

        self::assertSame($attribute, $result);
    }

    public function testThrowsExceptionIfAttributeDoesNotExist(): void
    {
        $notFoundUlid = AttributeMother::DEFAULT_ULID;

        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->with(self::callback(fn (Ulid $ulid) => $ulid->value() === $notFoundUlid))
            ->willThrowException(AttributeNotFoundException::withUlid($notFoundUlid));

        $query = $this->fillAndGetQuery(ulid: $notFoundUlid);

        $this->expectException(AttributeNotFoundException::class);

        $this->createHandler()($query);
    }

    private function fillAndGetQuery(string $ulid): GetAttributeItemQuery
    {
        return new GetAttributeItemQuery($ulid);
    }

    private function createHandler(): GetAttributeItemHandler
    {
        return new GetAttributeItemHandler(readRepository: $this->readRepository);
    }
}
