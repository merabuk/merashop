<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeChangedException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidator;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

final class AttributeValidatorTest extends BaseUnitTest
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

    #[DataProvider('updateAttributeProvider')]
    public function testItValidatesUpdating(
        Attribute $attribute,
        Code $newCode,
        Type $newType,
        array $optionsUlids,
    ): void {
        $attribute->getCode()->equals($newCode)
            ? $this->checkCodeNeverCalled()
            : $this->givenCodeIsAvailable($newCode);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $newType,
            optionsUlids: $optionsUlids,
        );
    }

    public static function updateAttributeProvider(): iterable
    {
        $attribute = AttributeMother::createWithData(
            code: 'old-code',
            type: TypeEnum::String,
            id: 123
        );
        $optionsUlids = self::getAttributeOptionsUlids($attribute);

        yield 'code changed' => [
            'attribute' => $attribute,
            'newCode' => Code::fromString('new-code'),
            'newType' => $attribute->getType(),
            'optionsUlids' => $optionsUlids,
        ];
        yield 'type changed' => [
            'attribute' => $attribute,
            'newCode' => $attribute->getCode(),
            'newType' => Type::fromEnum(TypeEnum::Text),
            'optionsUlids' => $optionsUlids,
        ];
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

        $this->expectException(AttributeTypeCanNotBeChangedException::class);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $newCode,
            newType: $newType,
            optionsUlids: self::getAttributeOptionsUlids($attribute),
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
            optionsUlids: self::getAttributeOptionsUlids($attribute),
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
            optionsUlids: self::getAttributeOptionsUlids($attribute),
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
            optionsUlids: self::getAttributeOptionsUlids($attribute),
        );
    }

    public function testThrowsExceptionWhenOneOfAttributeOptionsNotFoundWhileUpdating(): void
    {
        $attribute = AttributeMother::createWithData(type: TypeEnum::String, id: 123);
        $optionsUlids = [AttributeOptionUlid::fromString('01KMDEC4Z9NSK4YPEW8NG5068T')];

        $this->checkCodeNeverCalled();

        $this->expectException(AttributeOptionNotFoundException::class);

        $this->createValidator()->validateUpdate(
            attribute: $attribute,
            version: $attribute->getVersion()->value(),
            newCode: $attribute->getCode(),
            newType: $attribute->getType(),
            optionsUlids: $optionsUlids,
        );
    }

    /**
     * @return AttributeOptionUlid[]
     */
    private static function getAttributeOptionsUlids(Attribute $attribute): array
    {
        return array_map(fn (AttributeOption $ao) => $ao->getUlid(), $attribute->getOptions()->all());
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
