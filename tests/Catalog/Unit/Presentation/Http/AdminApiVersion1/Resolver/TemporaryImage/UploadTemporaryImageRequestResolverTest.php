<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resolver\TemporaryImage\UploadTemporaryImageRequestResolver;
use App\Shared\Domain\Service\Validation\ImageValidatorInterface;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Tests\Shared\Support\Traits\ValidatorHelperTrait;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UploadTemporaryImageRequestResolverTest extends TestCase
{
    use ValidatorHelperTrait;
    use VfsStreamTrait;

    private ImageValidatorInterface&MockObject $imageValidator;

    protected function setUp(): void
    {
        $this->setupVfs('resolver_test');
        $this->setValidator();
        $this->setContextualValidator();
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

        $fileKey = UploadTemporaryImageRequest::getFileKey();
        $contextKey = UploadTemporaryImageRequest::getContextKey();
        $context = ContextEnum::ProductMain;

        $request = new Request(
            request: [$contextKey => $context->value],
            files: [$fileKey => $uploadedFile]
        );

        $argument = new ArgumentMetadata(
            name: 'request',
            type: UploadTemporaryImageRequest::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null
        );

        $this->validator->expects(self::once())
            ->method('startContext')
            ->willReturn($this->contextualValidator);

        $this->makeContextValidationAssertions(
            fileKey: $fileKey,
            contextKey: $contextKey,
            request: $request
        );

        $this->imageValidator->expects(self::once())
            ->method('validate')
            ->with(
                self::callback(function (RawFile $file) use ($uploadedFile): bool {
                    return $uploadedFile->getRealPath() === $file->getLocalPath()
                        && $uploadedFile->getClientOriginalName() === $file->getOriginalName()
                        && $uploadedFile->guessExtension() === $file->getExtension()
                        && $uploadedFile->getMimeType() === $file->getMimeType();
                }),
                self::equalTo($context->value),
                self::equalTo($fileKey)
            );

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

    private function makeContextValidationAssertions(
        string $fileKey,
        string $contextKey,
        Request $request,
        int $violationsCount = 0,
    ): void {
        $this->contextualValidator->expects(self::exactly(2))
            ->method('atPath')
            ->with(self::logicalOr(
                self::equalTo($fileKey),
                self::equalTo($contextKey)
            ))
            ->willReturnSelf();

        $imageFile = $request->files->get($fileKey);
        $collectionValue = $request->request->get($contextKey);

        $this->contextualValidator->expects(self::exactly(2))
            ->method('validate')
            ->willReturnCallback(function (mixed $value, array $constraints) use ($imageFile, $collectionValue) {
                if ($value === $imageFile) {
                    $this->assertFileConstraints($constraints);
                } elseif ($value === $collectionValue) {
                    $this->assertCollectionConstraints($constraints);
                } else {
                    self::fail('Contextual validator received unexpected value for validation');
                }

                return $this->contextualValidator;
            })
            ->willReturnSelf();

        $this->contextualValidator->expects(self::once())
            ->method('getViolations')
            ->willReturn($this->makeViolations($violationsCount));
    }

    private function assertFileConstraints(array $constraints): void
    {
        self::assertCount(2, $constraints);
        self::assertInstanceOf(NotBlank::class, $constraints[0]);
        self::assertInstanceOf(File::class, $constraints[1]);
    }

    private function assertCollectionConstraints(array $constraints): void
    {
        self::assertCount(2, $constraints);
        self::assertInstanceOf(NotBlank::class, $constraints[0]);
        self::assertInstanceOf(Choice::class, $constraints[1]);
        self::assertSame(
            UploadTemporaryImageRequest::getAvailableContexts(),
            $constraints[1]->choices
        );
    }

    private function createResolver(): UploadTemporaryImageRequestResolver
    {
        return new UploadTemporaryImageRequestResolver(
            validator: $this->validator,
            imageValidator: $this->imageValidator
        );
    }
}
