<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Shared\Domain\Exception\Services\Storage\InvalidImageException;
use App\Shared\Domain\Exception\ValueObject\InvalidRawFileException;
use App\Shared\Domain\Service\Validation\ImageValidatorInterface;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Infrastructure\Exception\Traits\UnprocessableEntityErrorTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UploadTemporaryImageRequestResolver implements ValueResolverInterface
{
    use UnprocessableEntityErrorTrait;

    public function __construct(
        private ValidatorInterface $validator,
        private ImageValidatorInterface $imageValidator,
    ) {
    }

    /**
     * @return iterable<UploadTemporaryImageRequest>
     *
     * @throws InvalidImageException
     * @throws InvalidRawFileException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UploadTemporaryImageRequest::class !== $argument->getType()) {
            return [];
        }

        $fileKey = UploadTemporaryImageRequest::getFileKey();
        $contextKey = UploadTemporaryImageRequest::getContextKey();

        /** @var UploadedFile $imageFile */
        $imageFile = $request->files->get($fileKey);
        $collection = $request->request->get($contextKey);

        $contextValidator = $this->validator->startContext();

        $contextValidator->atPath($fileKey)->validate($imageFile, [
            new Assert\NotBlank(),
            new Assert\File(),
        ]);

        $contextValidator->atPath($contextKey)->validate($collection, [
            new Assert\NotBlank(),
            new Assert\Choice(choices: UploadTemporaryImageRequest::getAvailableContexts()),
        ]);

        $violations = $contextValidator->getViolations();

        if ($violations->count() > 0) {
            throw $this->makeSystemValidationException(message: 'Upload temporary image request validation failed', value: $request, violations: $violations);
        }

        $rawFile = RawFile::fromPath(
            localPath: (string) $imageFile->getRealPath(),
            originalName: $imageFile->getClientOriginalName(),
            extension: $imageFile->guessExtension(),
            mimeType: $imageFile->getMimeType()
        );

        $contextEnum = ContextEnum::from((string) $collection);
        $this->imageValidator->validate(file: $rawFile, context: $contextEnum->value, propertyPath: $fileKey);

        yield new UploadTemporaryImageRequest(file: $rawFile, context: $contextEnum);
    }
}
