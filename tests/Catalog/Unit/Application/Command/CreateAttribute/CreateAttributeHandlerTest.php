<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class CreateAttributeHandlerTest extends TestCase
{
    /**
     * @throws AttributeAlreadyExistsException
     * @throws CreateAttributeException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testHandleSuccess(): void
    {
        $readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
        $ulidGenerator = $this->createMock(UlidGeneratorInterface::class);

        $command = new CreateAttributeCommand(
            code: 'color',
            type: 'string',
            translations: ['en' => ['name' => 'Color']],
            adminUlid: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );

        $fakeId = 123;
        $ulid = '01KHVRCA0FCCYAQT1P88R317DD';
        $ulidGenerator->method('next')->willReturn($ulid);
        $readRepository->method('existsByCode')->willReturn(false);

        $writeRepository->expects($this->once())
            ->method('save')
            ->willReturn($this->makeSavedAttribute(id: $fakeId, ulid: $ulid, command: $command));

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
    public function testHandleThrowsExceptionIfAttributeExists(): void
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

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function makeSavedAttribute(
        int $id,
        string $ulid,
        CreateAttributeCommand $command,
    ): Attribute {
        return new Attribute(
            id: Id::fromInt($id),
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($command->code),
            type: Type::fromString($command->type),
            translations: Translations::fromArray($command->translations),
            version: Version::initial(),
            createdBy: AdminUlid::fromString($command->adminUlid)
        );
    }
}
