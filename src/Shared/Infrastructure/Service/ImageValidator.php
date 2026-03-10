<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\ImageConstraintsRegistryInterface;
use App\Shared\Domain\Service\ImageValidatorInterface;
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

    public function validate(RawFile $file, string $context): void
    {
        $constraints = $this->registry->getConstraints($context);

        $violations = $this->validator->validate($file->getLocalPath(), [
            new Assert\Image(
                maxSize: $constraints->maxSize,
                mimeTypes: $constraints->allowedMimeTypes,
                maxWidth: $constraints->maxWidth,
                maxHeight: $constraints->maxHeight,
                detectCorrupted: $constraints->detectCorrupted
            ),
        ]);

        if ($violations->count() > 0) {
            throw $this->makeSystemValidationException(message: 'Image validation failed', value: $file->getLocalPath(), violations: $violations);
        }
    }
}
