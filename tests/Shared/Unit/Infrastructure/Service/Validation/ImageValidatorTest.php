<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Validation;

use App\Shared\Domain\Enum\MimeTypeEnum;
use App\Shared\Domain\Service\Image\ImageConstraintsRegistryInterface;
use App\Shared\Domain\ValueObject\File\ImageConstraints;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Infrastructure\Service\Validation\ImageValidator;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImageValidatorTest extends TestCase
{
    use VfsStreamTrait;

    private ValidatorInterface&MockObject $validator;
    private ContextualValidatorInterface&MockObject $contextualValidator;
    private ImageConstraintsRegistryInterface&MockObject $registry;

    public function setUp(): void
    {
        $this->setupVfs('image_validator_test');
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->registry = $this->createMock(ImageConstraintsRegistryInterface::class);
    }

    public function testItValidatesImageCorrectly(): void
    {
        $file = $this->getRawFile();
        $propertyPath = 'valid-property-path';
        $context = 'valid-context';

        $constraintsDTO = $this->getConstraints();

        $this->registry->expects(self::once())
            ->method('getConstraints')
            ->with(self::equalTo($context))
            ->willReturn($constraintsDTO);

        $this->validator->expects(self::once())
            ->method('startContext')
            ->willReturn($this->contextualValidator);

        $this->makeContextValidationAssertions(
            file: $file,
            propertyPath: $propertyPath,
            constraintsDTO: $this->getConstraints()
        );

        $this->createValidator()->validate(file: $file, context: $context, propertyPath: $propertyPath);
    }

    public function testThrowsExceptionWhenViolationsExists(): void
    {
        $file = $this->getRawFile();
        $propertyPath = 'valid-property-path';
        $context = 'valid-context';

        $this->registry->expects(self::once())
            ->method('getConstraints')
            ->with(self::equalTo($context))
            ->willReturn($this->getConstraints());

        $this->validator->expects(self::once())
            ->method('startContext')
            ->willReturn($this->contextualValidator);

        $this->makeContextValidationAssertions(
            file: $file,
            propertyPath: $propertyPath,
            constraintsDTO: $this->getConstraints(),
            violationsCount: 1
        );

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Image validation failed');

        $this->createValidator()->validate(file: $file, context: $context, propertyPath: $propertyPath);
    }

    private function getRawFile(): RawFile
    {
        return RawFile::fromPath(
            localPath: $this->createVirtualFile(name: 'test.png', content: 'test-content'),
            originalName: 'test.png',
            extension: 'png',
            mimeType: MimeTypeEnum::Png->value
        );
    }

    private function getConstraints(): ImageConstraints
    {
        return new ImageConstraints(
            maxSize: 2 * 1024 * 1024, // 2MB,
            allowedMimeTypes: [
                MimeTypeEnum::Png->value,
            ],
            minWidth: 100,
            minHeight: 100,
            maxWidth: 1000,
            maxHeight: 1000
        );
    }

    private function makeContextValidationAssertions(
        RawFile $file,
        string $propertyPath,
        ImageConstraints $constraintsDTO,
        int $violationsCount = 0,
    ): void {
        $this->contextualValidator->expects(self::once())
            ->method('atPath')
            ->with(self::equalTo($propertyPath))
            ->willReturnSelf();
        $this->contextualValidator->expects(self::once())
            ->method('validate')
            ->with(
                self::equalTo($file->getLocalPath()),
                self::callback(function (array $constraints) use ($constraintsDTO) {
                    $imageConstraint = $constraints[0];

                    return $imageConstraint instanceof Image
                        && $imageConstraint->maxSize === $constraintsDTO->maxSize
                        && $imageConstraint->mimeTypes === $constraintsDTO->allowedMimeTypes
                        && $imageConstraint->minWidth === $constraintsDTO->minWidth
                        && $imageConstraint->maxWidth === $constraintsDTO->maxWidth
                        && $imageConstraint->minHeight === $constraintsDTO->minHeight
                        && $imageConstraint->maxHeight === $constraintsDTO->maxHeight
                        && $imageConstraint->detectCorrupted === $constraintsDTO->detectCorrupted;
                })
            )
            ->willReturnSelf();
        $this->contextualValidator->expects(self::once())
            ->method('getViolations')
            ->willReturn($this->makeViolations($violationsCount));
    }

    private function makeViolations(int $count = 0): ConstraintViolationListInterface
    {
        $mock = $this->createMock(ConstraintViolationListInterface::class);

        $mock->method('count')->willReturn($count);

        return $mock;
    }

    private function createValidator(): ImageValidator
    {
        return new ImageValidator(validator: $this->validator, registry: $this->registry);
    }
}
