<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UpdateAttribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeHandler;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactoryInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeChangedException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Catalog\Support\Traits\AttributeHelperTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

final class UpdateAttributeHandlerTest extends TestCase
{
    use AttributeHelperTrait;

    private AttributeReadRepositoryInterface&MockObject $readRepository;
    private AttributeValidatorInterface&MockObject $attributeValidator;
    private AttributeApplicationFactoryInterface&MockObject $attributeFactory;
    private AttributeWriteRepositoryInterface&MockObject $writeRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->attributeValidator = $this->createMock(AttributeValidatorInterface::class);
        $this->attributeFactory = $this->createMock(AttributeApplicationFactoryInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
    }

    #[DataProvider('attributeDataProvider')]
    public function testHandleSuccess(
        Attribute $existingAttribute,
        Attribute $expectedAttribute,
        ?OptionCollection $givenFromUIOptions = null,
        array $newOptionUlids = [],
    ): void {
        $command = $this->fillAndGetUpdateCommand(
            attribute: $expectedAttribute,
            options: $givenFromUIOptions,
            newOptionUlids: $newOptionUlids,
        );

        $expectedAttributeOptionUlids = $this->getExpectedAttributeOptionUlids($expectedAttribute);

        $this->exceptMapAttributeOptionUlids(input: $command->options, result: $expectedAttributeOptionUlids);
        $this->expectAttributeFound(attribute: $existingAttribute, attributeUlid: $command->ulid);
        $this->expectPassValidation(
            attribute: $existingAttribute,
            command: $command,
            optionsUlids: $expectedAttributeOptionUlids,
        );
        $this->expectFactoryUpdateAttribute(
            attribute: $existingAttribute,
            command: $command,
            expectedState: $expectedAttribute,
        );
        $this->expectSaveAttribute(expectedAttribute: $expectedAttribute);

        $this->createHandler()($command);
    }

    public static function attributeDataProvider(): iterable
    {
        yield 'code changed' => [
            'existingAttribute' => AttributeMother::createWithData(code: 'old-code', id: 123),
            'expectedAttribute' => AttributeMother::createWithData(
                code: 'new-code',
                updatedByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
                id: 123
            ),
        ];
        yield 'type changed' => [
            'existingAttribute' => AttributeMother::createWithData(type: TypeEnum::String, id: 123),
            'expectedAttribute' => AttributeMother::createWithData(
                type: TypeEnum::Text,
                updatedByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
                id: 123
            ),
        ];
        yield 'with options' => [
            'existingAttribute' => AttributeMother::createWithData(type: TypeEnum::Select, options: [
                AttributeOptionMother::createWithData(
                    ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
                    code: 'option-code-1',
                    id: 456,
                ),
                AttributeOptionMother::createWithData(
                    ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P',
                    code: 'option-code-2',
                    id: 789,
                ),
            ], id: 123),
            'expectedAttribute' => AttributeMother::createWithData(type: TypeEnum::Select, options: [
                AttributeOptionMother::createWithData(
                    ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
                    code: 'option-code-1',
                    id: 456,
                ),
                AttributeOptionMother::createWithData(
                    ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P',
                    code: 'option-code-2',
                    isActive: false,
                    id: 789,
                ),
                AttributeOptionMother::createWithData(
                    ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
                    code: 'option-code-3',
                    id: 321,
                ),
            ], updatedByUlid: AttributeMother::DEFAULT_ADMIN_ULID, id: 123),
            'givenFromUIOptions' => OptionCollection::fromArray([
                AttributeOptionMother::createWithData(
                    ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
                    code: 'option-code-1',
                    id: 456,
                ),
                AttributeOptionMother::createWithData(
                    ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
                    code: 'option-code-3',
                    id: 321,
                ),
            ]),
            'newOptionUlids' => [
                '01KMJ1ANFES9VS1HYEYBDCNCFW',
            ],
        ];
    }

    public function testThrowsExceptionIfAttributeDoesNotExist(): void
    {
        $attribute = AttributeMother::createWithData(id: 123);
        $command = $this->fillAndGetUpdateCommand(attribute: $attribute);

        $this->expectAttributeNotFound($attribute->getUlid());
        $this->mapAttributeOptionUlidsNeverCalled();
        $this->attributeValidatorNeverCalled();
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeNotFoundException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionWhenTypeCanNotBeChanged(): void
    {
        $attribute = AttributeMother::createWithData(type: TypeEnum::Color, id: 123);
        $command = $this->fillAndGetUpdateCommand(attribute: $attribute, type: TypeEnum::String);

        $expectedAttributeOptionUlids = $this->getExpectedAttributeOptionUlids($attribute);

        $this->expectAttributeFound(attribute: $attribute, attributeUlid: $command->ulid);
        $this->exceptMapAttributeOptionUlids(input: $command->options, result: $expectedAttributeOptionUlids);
        $this->givenTypeCanNotBeChanged(
            attribute: $attribute,
            command: $command,
            optionsUlids: $expectedAttributeOptionUlids,
        );
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeTypeCanNotBeChangedException::class);

        $this->createHandler()($command);
    }

    public function testThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $attribute = AttributeMother::createWithData(version: 2, id: 123);
        $command = $this->fillAndGetUpdateCommand(
            attribute: $attribute,
            version: $attribute->getVersion()->value() + 1
        );

        $expectedAttributeOptionUlids = $this->getExpectedAttributeOptionUlids($attribute);

        $this->expectAttributeFound(attribute: $attribute, attributeUlid: $command->ulid);
        $this->exceptMapAttributeOptionUlids(input: $command->options, result: $expectedAttributeOptionUlids);
        $this->givenVersionIsInvalid(
            attribute: $attribute,
            command: $command,
            optionsUlids: $expectedAttributeOptionUlids,
        );
        $this->saveAttributeNeverCalled();

        $this->expectException(ConcurrencyException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfCodeAttributeExists(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old-code', id: 123);
        $command = $this->fillAndGetUpdateCommand(attribute: $attribute, code: 'existing-code');

        $expectedAttributeOptionUlids = $this->getExpectedAttributeOptionUlids($attribute);

        $this->expectAttributeFound(attribute: $attribute, attributeUlid: $command->ulid);
        $this->exceptMapAttributeOptionUlids(input: $command->options, result: $expectedAttributeOptionUlids);
        $this->givenCodeIsTaken(
            attribute: $attribute,
            command: $command,
            optionsUlids: $expectedAttributeOptionUlids,
        );
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionWhenOptionUlidNotFound(): void
    {
        $attribute = AttributeMother::createWithData(id: 123);
        $options = OptionCollection::fromArray([
            AttributeOptionMother::createWithData(id: 456),
        ]);
        $command = $this->fillAndGetUpdateCommand(attribute: $attribute, options: $options);

        $expectedAttributeOptionUlids = $this->getExpectedAttributeOptionUlids(
            attribute: $attribute,
            options: $options
        );

        $this->expectAttributeFound(attribute: $attribute, attributeUlid: $command->ulid);
        $this->exceptMapAttributeOptionUlids(input: $command->options, result: $expectedAttributeOptionUlids);
        $this->givenOptionUlidNotFound(
            attribute: $attribute,
            command: $command,
            optionsUlids: $expectedAttributeOptionUlids,
        );
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeOptionNotFoundException::class);

        $this->createHandler()($command);
    }

    private function createHandler(): UpdateAttributeHandler
    {
        return new UpdateAttributeHandler(
            readRepository: $this->readRepository,
            attributeValidator: $this->attributeValidator,
            attributeFactory: $this->attributeFactory,
            writeRepository: $this->writeRepository
        );
    }

    private function expectAttributeFound(Attribute $attribute, string $attributeUlid): void
    {
        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->with(self::callback(fn (Ulid $ulid) => $ulid->value() === $attributeUlid))
            ->willReturn($attribute);
    }

    private function expectAttributeNotFound(Ulid $attributeUlid): void
    {
        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->with(self::callback(fn (Ulid $ulid) => $ulid->equals($attributeUlid)))
            ->willThrowException(AttributeNotFoundException::withUlid($attributeUlid->value()));
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function expectPassValidation(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
    ): void {
        $this->expectValidationCheck(attribute: $attribute, command: $command, optionsUlids: $optionsUlids);
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function givenTypeCanNotBeChanged(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
    ): void {
        $this->expectValidationCheck(
            attribute: $attribute,
            command: $command,
            optionsUlids: $optionsUlids,
            exception: new AttributeTypeCanNotBeChangedException()
        );
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function givenVersionIsInvalid(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
    ): void {
        $this->expectValidationCheck(
            attribute: $attribute,
            command: $command,
            optionsUlids: $optionsUlids,
            exception: new ConcurrencyException()
        );
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function givenCodeIsTaken(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
    ): void {
        $this->expectValidationCheck(
            attribute: $attribute,
            command: $command,
            optionsUlids: $optionsUlids,
            exception: new AttributeAlreadyExistsException()
        );
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function givenOptionUlidNotFound(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
        int $index = 0,
    ): void {
        $this->expectValidationCheck(
            attribute: $attribute,
            command: $command,
            optionsUlids: $optionsUlids,
            exception: AttributeOptionNotFoundException::withUlid($optionsUlids[$index]->value())
        );
    }

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     */
    private function expectValidationCheck(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        array $optionsUlids,
        ?Throwable $exception = null,
    ): void {
        $invokeContext = $this->attributeValidator->expects(self::once())
            ->method('validateUpdate')
            ->with(
                self::equalTo($attribute),
                self::equalTo($command->version),
                self::callback(fn (Code $c) => $c->value() === $command->code),
                self::callback(fn (Type $t) => $t->value()->value === $command->type),
                self::equalTo($optionsUlids),
            );

        if ($exception) {
            $invokeContext->willThrowException($exception);
        }
    }

    private function attributeValidatorNeverCalled(): void
    {
        $this->attributeValidator->expects(self::never())->method('validateUpdate');
    }

    /**
     * @param AttributeOptionData[] $input
     * @param AttributeOptionUlid[] $result
     */
    private function exceptMapAttributeOptionUlids(array $input, array $result): void
    {
        $this->attributeFactory->expects(self::once())
            ->method('mapAttributeOptionUlids')
            ->with(self::equalTo($input))
            ->willReturn($result);
    }

    private function mapAttributeOptionUlidsNeverCalled(): void
    {
        $this->attributeFactory->expects(self::never())->method('mapAttributeOptionUlids');
    }

    private function expectFactoryUpdateAttribute(
        Attribute $attribute,
        UpdateAttributeCommand $command,
        Attribute $expectedState,
    ): void {
        $this->attributeFactory->expects(self::once())
            ->method('updateFromCommand')
            ->with(
                self::equalTo($attribute),
                self::equalTo($command),
            )
            ->willReturnCallback(function (
                Attribute $updatedAttribute,
                UpdateAttributeCommand $cmd,
            ) use ($expectedState) {
                $updatedAttribute->update(
                    code: $expectedState->getCode(),
                    type: $expectedState->getType(),
                    translations: $expectedState->getTranslations(),
                    updatedBy: $expectedState->getUpdatedBy(),
                    options: $expectedState->getOptions()
                );
            });
    }

    private function expectSaveAttribute(Attribute $expectedAttribute): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Attribute $actualAttribute) use ($expectedAttribute) {
                self::assertTrue($expectedAttribute->getCode()->equals($actualAttribute->getCode()));
                self::assertTrue($expectedAttribute->getType()->equals($actualAttribute->getType()));
                self::assertTrue($expectedAttribute->getTranslations()->equals($actualAttribute->getTranslations()));
                self::assertNotNull($expectedAttribute->getUpdatedBy());
                self::assertNotNull($actualAttribute->getUpdatedBy());
                self::assertTrue($expectedAttribute->getUpdatedBy()->equals($actualAttribute->getUpdatedBy()));
                self::assertEquals($expectedAttribute->getOptions(), $actualAttribute->getOptions());

                return true;
            }))
            ->willReturnArgument(0);
    }

    private function saveAttributeNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }
}
