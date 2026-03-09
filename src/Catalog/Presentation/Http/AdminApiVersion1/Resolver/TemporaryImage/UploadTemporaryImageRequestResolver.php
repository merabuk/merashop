<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage;

use App\Catalog\Domain\Enum\ImageContextEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Shared\Domain\Exception\Services\Storage\InvalidImageException;
use App\Shared\Domain\Exception\ValueObject\InvalidRawFileException;
use App\Shared\Domain\Service\ImageValidatorInterface;
use App\Shared\Domain\ValueObject\RawFile;
use App\Shared\Infrastructure\Exception\Traits\UnprocessableEntityErrorTrait;
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

        $imageFile = $request->files->get('image');
        $collection = $request->request->get('collection');

        $baseViolations = $this->validator->validate(
            value: [
                'image' => $imageFile,
                'collection' => $collection,
            ],
            constraints: new Assert\Collection([
                'image' => [
                    new Assert\NotBlank(),
                    new Assert\File(),
                ],
                'collection' => [
                    new Assert\NotBlank(),
                    new Assert\Choice(choices: UploadTemporaryImageRequest::getAvailableContexts()),
                ],
            ])
        );

        if ($baseViolations->count() > 0) {
            throw $this->makeSystemValidationException(message: 'Upload temporary image request validation failed', value: $request, violations: $baseViolations);
        }

        $rawFile = RawFile::fromPath(
            localPath: $imageFile->getRealPath(),
            originalName: $imageFile->getClientOriginalName(),
            extension: $imageFile->guessExtension(),
            mimeType: $imageFile->getMimeType()
        );

        $context = ImageContextEnum::from((string) $collection);
        $this->imageValidator->validate(file: $rawFile, context: $context->value);

        yield new UploadTemporaryImageRequest(file: $rawFile, context: $context);
    }
}
