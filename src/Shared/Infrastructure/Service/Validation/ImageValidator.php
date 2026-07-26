<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service\Validation;

use App\Shared\Domain\Service\Image\ImageConstraintsRegistryInterface;
use App\Shared\Domain\Service\Validation\ImageValidatorInterface;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Infrastructure\Exception\Traits\UnprocessableEntityErrorTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ImageValidator implements ImageValidatorInterface
{
    use UnprocessableEntityErrorTrait;

    public function __construct(
        private ValidatorInterface $validator,
        private ImageConstraintsRegistryInterface $registry,
    ) {
    }

    public function validate(RawFile $file, string $context, string $propertyPath = 'image'): void
    {
        $constraints = $this->registry->getConstraints($context);

        $validatorContext = $this->validator->startContext();

        $validatorContext->atPath($propertyPath)->validate($file->getLocalPath(), [
            new Assert\Image(
                maxSize: $constraints->getMaxSize(),
                mimeTypes: $constraints->allowedMimeTypes,
                minWidth: $constraints->minWidth,
                maxWidth: $constraints->getMaxWidth(),
                maxHeight: $constraints->getMaxHeight(),
                minHeight: $constraints->minHeight,
                detectCorrupted: $constraints->detectCorrupted
            ),
        ]);

        $violations = $validatorContext->getViolations();

        if ($violations->count() > 0) {
            throw $this->makeSystemValidationException(message: 'Image validation failed', value: $file->getLocalPath(), violations: $violations);
        }
    }
}
