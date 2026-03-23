<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeCahngedException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidator;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AttributeValidatorTest extends TestCase
{
    private AttributeReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
    }

    public function testItValidatesCreation(): void
    {
        $code = Code::fromString('attribute-code');

        $this->givenCodeIsAvailable($code);

        $this->createValidator()->validateCreation(code: $code);
    }

    public function testThrowsExceptionWhenAttributeCodeAlreadyExists(): void
    {
        $code = Code::fromString('existing-code');

        $this->givenCodeIsTaken($code);

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createValidator()->validateCreation(code: $code);
    }

    public function testItValidatesUpdating(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $newCode = Code::fromString('new-code');

        $this->givenCodeIsAvailable($newCode);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $attribute->getType(),
        );
    }

    public function testThrowsExceptionWhenAttributeCannotChangeType(): void
    {
        $attribute = AttributeMother::createWithData(
            code: 'old-code',
            type: TypeEnum::String,
            id: 123
        );
        $newCode = Code::fromString('new-code');
        $newType = Type::fromEnum(TypeEnum::Integer);

        $this->expectException(AttributeTypeCanNotBeCahngedException::class);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $newType,
        );
    }

    public function testThrowsExceptionWhenAttributeVersionDoesNotMatch(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $newCode = Code::fromString('new-code');

        $this->expectException(ConcurrencyException::class);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value() + 1,
            newCode: $newCode,
            newType: $attribute->getType(),
        );
    }

    public function testThrowsExceptionWhenAttributeCodeAlreadyExistsForUpdating(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $newCode = Code::fromString('existing-code');

        $this->givenCodeIsTaken($newCode);

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $attribute->getType(),
        );
    }

    public function testItSkipsCodeCheckWhenCodeNotChangedForUpdating(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $newCode = Code::fromString('old-code');

        $this->checkCodeNeverCalled();

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $attribute->getType(),
        );
    }

    private function createValidator(): AttributeValidator
    {
        return new AttributeValidator(readRepository: $this->readRepository);
    }

    private function givenCodeIsAvailable(Code $code): void
    {
        $this->expectCodeCheck($code, false);
    }

    private function givenCodeIsTaken(Code $code): void
    {
        $this->expectCodeCheck($code, true);
    }

    private function expectCodeCheck(Code $code, bool $exists): void
    {
        $this->readRepository->expects(self::once())
            ->method('existsByCode')
            ->with(self::equalTo($code))
            ->willReturn($exists);
    }

    private function checkCodeNeverCalled(): void
    {
        $this->readRepository->expects(self::never())->method('existsByCode');
    }
}
