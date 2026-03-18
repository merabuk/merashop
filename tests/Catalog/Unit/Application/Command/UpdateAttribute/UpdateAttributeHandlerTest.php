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
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

final class UpdateAttributeHandlerTest extends TestCase
{
    private AttributeReadRepositoryInterface&MockObject $readRepository;
    private AttributeValidatorInterface&MockObject $attributeValidator;
    private AttributeWriteRepositoryInterface&MockObject $writeRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->attributeValidator = $this->createMock(AttributeValidatorInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
    }

    public function testHandleSuccess(): void
    {
        $newCode = 'updated-code';
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $command = $this->fillAndGetCommand(attribute: $attribute, code: $newCode);

        $this->expectAttributeFound($attribute, $command->id);
        $this->givenCodeIsAvailable($attribute, $command->version, $newCode);
        $this->expectSaveAttribute($command, $attribute);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfAttributeDoesNotExist(): void
    {
        $attribute = AttributeMother::createWithData(id: 123);
        $command = $this->fillAndGetCommand(attribute: $attribute);

        $this->expectAttributeNotFound($attribute->getId());
        $this->attributeValidatorNeverCalled();
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeNotFoundException::class);

        $this->createHandler()($command);
    }

    public function testThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $attribute = AttributeMother::createWithData(version: 2, id: 123);
        $invalidVersion = $attribute->getVersion()->value() + 1;
        $command = $this->fillAndGetCommand(attribute: $attribute, version: $invalidVersion);

        $this->expectAttributeFound($attribute, $command->id);
        $this->givenVersionIsInvalid($attribute, $invalidVersion, $command->code);
        $this->saveAttributeNeverCalled();

        $this->expectException(ConcurrencyException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfCodeAttributeExists(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $command = $this->fillAndGetCommand(attribute: $attribute, code: 'existing-code');

        $this->expectAttributeFound($attribute, $command->id);
        $this->givenCodeIsTaken($attribute, $attribute->getVersion()->value(), $command->code);
        $this->saveAttributeNeverCalled();

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
            attributeValidator: $this->attributeValidator,
            writeRepository: $this->writeRepository
        );
    }

    private function expectAttributeFound(Attribute $attribute, int $attributeId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->value() === $attributeId))
            ->willReturn($attribute);
    }

    private function expectAttributeNotFound(Id $attributeId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->equals($attributeId)))
            ->willThrowException(new AttributeNotFoundException());
    }

    private function givenCodeIsAvailable(Attribute $attribute, int $version, string $code): void
    {
        $this->expectValidationCheck($attribute, $version, $code);
    }

    private function givenCodeIsTaken(Attribute $attribute, int $version, string $code): void
    {
        $this->expectValidationCheck($attribute, $version, $code, new AttributeAlreadyExistsException());
    }

    private function givenVersionIsInvalid(Attribute $attribute, int $version, string $code): void
    {
        $this->expectValidationCheck($attribute, $version, $code, new ConcurrencyException());
    }

    private function expectValidationCheck(
        Attribute $attribute,
        int $version,
        string $code,
        ?Throwable $exception = null,
    ): void {
        $invokeContext = $this->attributeValidator->expects(self::once())
            ->method('validateUpdate')
            ->with(
                self::equalTo($attribute),
                self::equalTo($version),
                self::callback(fn (Code $c) => $c->value() === $code)
            );

        if ($exception) {
            $invokeContext->willThrowException($exception);
        }
    }

    private function attributeValidatorNeverCalled(): void
    {
        $this->attributeValidator->expects(self::never())->method('validateUpdate');
    }

    private function expectSaveAttribute(UpdateAttributeCommand $command, Attribute $attribute): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Attribute $updatedAttribute) use ($command) {
                $codeCorrect = $command->code === $updatedAttribute->getCode()->value();
                $typeCorrect = $command->type === $updatedAttribute->getType()->value()->value;
                $translationsCorrect = $command->translations === $updatedAttribute->getTranslations()->toArray();
                $adminUlidCorrect = $command->adminUlid === $updatedAttribute->getCreatedBy()->value();

                return $codeCorrect && $typeCorrect && $translationsCorrect && $adminUlidCorrect;
            }))
            ->willReturn($attribute);
    }

    private function saveAttributeNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }
}
