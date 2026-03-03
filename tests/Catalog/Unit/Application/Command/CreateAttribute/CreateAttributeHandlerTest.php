<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\TestCase;

final class CreateAttributeHandlerTest extends TestCase
{
    /**
     * @throws AttributeAlreadyExistsException
     * @throws CreateAttributeException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testItHandleSuccess(): void
    {
        $readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
        $ulidGenerator = $this->createMock(UlidGeneratorInterface::class);

        $attributeType = TypeEnum::String;

        $command = new CreateAttributeCommand(
            code: 'color',
            type: $attributeType->value,
            translations: ['en' => ['name' => 'Color']],
            adminUlid: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );

        $fakeId = 123;
        $ulid = '01KHVRCA0FCCYAQT1P88R317DD';
        $ulidGenerator->method('next')->willReturn($ulid);
        $readRepository->method('existsByCode')->willReturn(false);

        $writeRepository->expects($this->once())
            ->method('save')
            ->willReturn(AttributeMother::createWithData(
                ulid: $ulid,
                code: $command->code,
                type: $attributeType,
                translations: $command->translations,
                createdByUlid: $command->adminUlid,
                id: $fakeId,
            ));

        $handler = new CreateAttributeHandler(
            readRepository: $readRepository,
            ulidGenerator: $ulidGenerator,
            writeRepository: $writeRepository
        );
        $resultId = $handler($command);

        self::assertSame($fakeId, $resultId);
    }

    /**
     * @throws CreateAttributeException
     */
    public function testThrowsExceptionIfAttributeExists(): void
    {
        $readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
        $ulidGenerator = $this->createMock(UlidGeneratorInterface::class);

        $command = new CreateAttributeCommand(
            code: 'duplicate',
            type: 'string',
            translations: ['en' => ['name' => 'Name']],
            adminUlid: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );

        $readRepository->method('existsByCode')->willReturn(true);
        $writeRepository->expects($this->never())->method('save');

        $this->expectException(AttributeAlreadyExistsException::class);

        $handler = new CreateAttributeHandler(
            readRepository: $readRepository,
            ulidGenerator: $ulidGenerator,
            writeRepository: $writeRepository
        );
        $handler($command);
    }
}
