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

        $ulidGenerator->method('next')->willReturn('01KHVRCA0FCCYAQT1P88R317DD');
        $readRepository->method('existsByCode')->willReturn(false);

        $writeRepository->expects($this->once())
            ->method('save')
            ->willReturn($this->makeSavedAttribute());

        $handler = new CreateAttributeHandler($readRepository, $ulidGenerator, $writeRepository);
        $handler($command);
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

        $handler = new CreateAttributeHandler($readRepository, $ulidGenerator, $writeRepository);
        $handler($command);
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function makeSavedAttribute(): Attribute
    {
        return new Attribute(
            id: Id::fromInt(123),
            ulid: Ulid::fromString('01KHVRCA0FCCYAQT1P88R317DD'),
            code: Code::fromString('color'),
            type: Type::fromString('string'),
            translations: Translations::fromArray(['en' => ['name' => 'Color']]),
            version: Version::initial(),
            createdBy: AdminUlid::fromString('01KHVRCA679BJ6PBXX5N3G6RR5')
        );
    }
}
