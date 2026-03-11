<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\TestCase;

final class CreateAttributeHandlerTest extends TestCase
{
    private AttributeReadRepositoryInterface $readRepository;
    private AttributeWriteRepositoryInterface $writeRepository;
    private UlidGeneratorInterface $ulidGenerator;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(id: $fakeId);

        $command = new CreateAttributeCommand(
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: $attribute->getTranslations()->toArray(),
            adminUlid: $attribute->getCreatedBy()->value()
        );

        $this->readRepository->expects(self::once())->method('existsByCode')->willReturn(false);
        $this->ulidGenerator->expects(self::once())->method('next')->willReturn($attribute->getUlid()->value());
        $this->writeRepository->expects(self::once())->method('save')->willReturn($attribute);

        $resultId = $this->createHandler()($command);

        self::assertSame($fakeId, $resultId);
    }

    public function testThrowsExceptionIfAttributeExists(): void
    {
        $command = new CreateAttributeCommand(
            code: 'duplicate',
            type: 'string',
            translations: ['en' => ['name' => 'Name']],
            adminUlid: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );

        $this->readRepository->expects(self::once())->method('existsByCode')->willReturn(true);
        $this->ulidGenerator->expects(self::never())->method('next');
        $this->writeRepository->expects(self::never())->method('save');

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    private function createHandler(): CreateAttributeHandler
    {
        return new CreateAttributeHandler(
            readRepository: $this->readRepository,
            ulidGenerator: $this->ulidGenerator,
            writeRepository: $this->writeRepository
        );
    }
}
