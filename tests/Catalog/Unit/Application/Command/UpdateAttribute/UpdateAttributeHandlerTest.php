<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UpdateAttribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeHandler;
use App\Catalog\Application\Exception\Attribute\UpdateAttributeException;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
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
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use PHPUnit\Framework\TestCase;

final class UpdateAttributeHandlerTest extends TestCase
{
    /**
     * @throws AttributeNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws UpdateAttributeException
     */
    public function testHandleThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);

        $existingAttribute = $this->makeAttribute(version: 2);
        $readRepository->method('getById')->willReturn($existingAttribute);

        $invalidVersion = $existingAttribute->getVersion()->value() + 1;

        $command = new UpdateAttributeCommand(
            id: $existingAttribute->getId()->value(),
            code: $existingAttribute->getCode()->value(),
            type: $existingAttribute->getType()->value()->value,
            translations: $existingAttribute->getTranslations()->toArray(),
            version: $invalidVersion,
            adminUlid: $existingAttribute->getCreatedBy()->value()
        );

        $this->expectException(ConcurrencyException::class);

        $handler = new UpdateAttributeHandler(
            readRepository: $readRepository,
            writeRepository: $writeRepository
        );
        $handler($command);
    }

    /**
     * @throws AttributeNotFoundException
     * @throws UpdateAttributeException
     * @throws InvalidCatalogValueObjectException
     * @throws ConcurrencyException
     * @throws InvalidLocaleException
     */
    public function testHandleSuccess(): void
    {
        $readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);

        $attribute = $this->makeAttribute();
        $readRepository->method('getById')->willReturn($attribute);

        $writeRepository->expects($this->once())->method('save')->willReturn($attribute);

        $command = new UpdateAttributeCommand(
            id: $attribute->getId()->value(),
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: $attribute->getTranslations()->toArray(),
            version: $attribute->getVersion()->value(),
            adminUlid: $attribute->getCreatedBy()->value()
        );

        $handler = new UpdateAttributeHandler(
            readRepository: $readRepository,
            writeRepository: $writeRepository
        );
        $handler($command);
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function makeAttribute(
        int $fakeId = 123,
        string $ulid = '01KHVRCA0FCCYAQT1P88R317DD',
        string $code = 'code',
        TypeEnum $type = TypeEnum::String,
        array $translations = ['en' => ['name' => 'Name']],
        int $version = 1,
        string $adminUlid = '01KHVRCA679BJ6PBXX5N3G6RR5',
    ): Attribute {
        return new Attribute(
            id: Id::fromInt($fakeId),
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($code),
            type: Type::fromEnum($type),
            translations: Translations::fromArray($translations),
            version: Version::fromInt($version),
            createdBy: AdminUlid::fromString($adminUlid)
        );
    }
}
