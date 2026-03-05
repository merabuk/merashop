<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UpdateAttribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeHandler;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\TestCase;

final class UpdateAttributeHandlerTest extends TestCase
{
    private AttributeReadRepositoryInterface $readRepository;
    private AttributeWriteRepositoryInterface $writeRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
    }

    public function testHandleSuccess(): void
    {
        $fakeId = 123;
        $newCode = 'updated-code';
        $attribute = AttributeMother::createWithData(code: 'old-code', id: $fakeId);

        $this->readRepository->expects(self::once())->method('getById')->willReturn($attribute);
        $this->readRepository->expects(self::never())->method('existsByCode');
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (Attribute $updatedAttribute) => $updatedAttribute->getCode()->value() === $newCode))
            ->willReturn($attribute);

        $command = $this->fillAndGetCommand(attribute: $attribute, code: $newCode);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfAttributeDoesNotExist(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(id: $fakeId);

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->willThrowException(new AttributeNotFoundException());
        $this->readRepository->expects(self::never())->method('existsByCode');
        $this->writeRepository->expects(self::never())->method('save');

        $command = $this->fillAndGetCommand(attribute: $attribute);

        $this->expectException(AttributeNotFoundException::class);

        $this->createHandler()($command);
    }

    public function testThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(version: 2, id: $fakeId);

        $this->readRepository->expects(self::once())->method('getById')->willReturn($attribute);
        $this->readRepository->expects(self::never())->method('existsByCode');
        $this->writeRepository->expects(self::never())->method('save');

        $invalidVersion = $attribute->getVersion()->value() + 1;

        $command = $this->fillAndGetCommand(attribute: $attribute, version: $invalidVersion);

        $this->expectException(ConcurrencyException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfAttributeExists(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(code: 'old-code', id: $fakeId);

        $this->readRepository->expects(self::once())->method('getById')->willReturn($attribute);
        $this->readRepository->expects(self::once())->method('existsByCode')->willReturn(true);
        $this->writeRepository->expects(self::never())->method('save');

        $command = $this->fillAndGetCommand(attribute: $attribute, code: 'new-code');

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    private function fillAndGetCommand(
        Attribute $attribute,
        ?string $code = null,
        ?int $version = null,
    ): UpdateAttributeCommand {
        return new UpdateAttributeCommand(
            id: $attribute->getId()->value(),
            code: $code ?? $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: $attribute->getTranslations()->toArray(),
            version: $version ?? $attribute->getVersion()->value(),
            adminUlid: $attribute->getCreatedBy()->value()
        );
    }

    private function createHandler(): UpdateAttributeHandler
    {
        return new UpdateAttributeHandler(
            readRepository: $this->readRepository,
            writeRepository: $this->writeRepository
        );
    }
}
