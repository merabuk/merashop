<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage\UploadTemporaryImageRequestResolver;
use App\Shared\Domain\Service\ImageValidatorInterface;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UploadTemporaryImageRequestResolverTest extends TestCase
{
    use VfsStreamTrait;

    private ValidatorInterface $validator;
    private ImageValidatorInterface $imageValidator;

    protected function setUp(): void
    {
        $this->setupVfs('resolver_test');
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->imageValidator = $this->createMock(ImageValidatorInterface::class);
    }

    public function testItResolvesRequest(): void
    {
        $extension = 'jpg';
        $fileName = sprintf('test.%s', $extension);
        $mimeType = 'image/jpeg';
        $localPath = $this->createVirtualFile(name: $fileName, content: 'content');

        $uploadedFile = $this->createMockUploadedFile(
            localPath: $localPath,
            originalName: $fileName,
            extension: $extension,
            mimeType: $mimeType
        );

        $context = ContextEnum::ProductMain;

        $request = new Request(
            request: [UploadTemporaryImageRequest::getContextKey() => $context->value],
            files: [UploadTemporaryImageRequest::getFileKey() => $uploadedFile]
        );

        $argument = new ArgumentMetadata(
            name: 'request',
            type: UploadTemporaryImageRequest::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null
        );

        $this->validator->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());
        $this->imageValidator->expects(self::once())->method('validate');

        $result = iterator_to_array($this->createResolver()->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertInstanceOf(UploadTemporaryImageRequest::class, $result[0]);
        self::assertSame($context, $result[0]->context);
        self::assertSame($localPath, $result[0]->file->getLocalPath());
        self::assertSame($fileName, $result[0]->file->getOriginalName());
        self::assertSame($extension, $result[0]->file->getExtension());
        self::assertSame($mimeType, $result[0]->file->getMimeType());
    }

    private function createMockUploadedFile(
        string $localPath,
        string $originalName = 'test.jpg',
        string $extension = 'jpg',
        string $mimeType = 'image/jpeg',
    ): UploadedFile {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getRealPath')->willReturn($localPath);
        $uploadedFile->method('getClientOriginalName')->willReturn($originalName);
        $uploadedFile->method('guessExtension')->willReturn($extension);
        $uploadedFile->method('getMimeType')->willReturn($mimeType);

        return $uploadedFile;
    }

    private function createResolver(): UploadTemporaryImageRequestResolver
    {
        return new UploadTemporaryImageRequestResolver(
            validator: $this->validator,
            imageValidator: $this->imageValidator
        );
    }
}
