<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Query\GetAttributeItem;

use App\Catalog\Application\Query\GetAttributeItem\GetAttributeItemHandler;
use App\Catalog\Application\Query\GetAttributeItem\GetAttributeItemQuery;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
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
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(id: $fakeId);

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with($attribute->getId())
            ->willReturn($attribute);

        $query = $this->fillAndGetQuery(id: $fakeId);

        $result = $this->createHandler()($query);

        self::assertSame($attribute, $result);
    }

    public function testThrowsExceptionIfAttributeDoesNotExist(): void
    {
        $fakeId = 123;

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->willThrowException(new AttributeNotFoundException());

        $query = $this->fillAndGetQuery(id: $fakeId);

        $this->expectException(AttributeNotFoundException::class);

        $this->createHandler()($query);
    }

    private function fillAndGetQuery(int $id): GetAttributeItemQuery
    {
        return new GetAttributeItemQuery($id);
    }

    private function createHandler(): GetAttributeItemHandler
    {
        return new GetAttributeItemHandler(readRepository: $this->readRepository);
    }
}
