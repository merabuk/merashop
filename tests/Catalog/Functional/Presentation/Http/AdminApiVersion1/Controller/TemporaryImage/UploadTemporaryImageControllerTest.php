<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\TemporaryImage\UploadTemporaryImageController;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Shared\Domain\Enum\MimeTypeEnum;
use App\Tests\Catalog\Support\Traits\CatalogStorageTestTrait;
use App\Tests\Catalog\Support\Traits\TemporaryImageFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiFileUploadTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UploadTemporaryImageControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiFileUploadTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use CatalogStorageTestTrait;
    use TemporaryImageFactoryTrait;

    private const string ROUTE_NAME = UploadTemporaryImageController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    protected function tearDown(): void
    {
        $this->cleanupUploadedFiles();
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestMultipart(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestMultipart(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItSuccessfullyUploadsTemporaryImage(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $fileKey = UploadTemporaryImageRequest::getFileKey();
        $contextKey = UploadTemporaryImageRequest::getContextKey();

        $uploadedFile = $this->createUploadedImage(
            mimeType: MimeTypeEnum::Png,
            originalName: 'my_product.png'
        );

        $this->requestMultipart(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            files: [$fileKey => $uploadedFile],
            parameters: [$contextKey => ContextEnum::ProductMain->value]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = $this->getResponseData($client);

        self::assertArrayHasKey('imageId', $data);

        $storagePath = $this->getReadRepository()->findByUlid(Ulid::fromString($data['imageId']))->getPath();
        $this->assertStorageHas($storagePath->value());
    }

    #[DataProvider('invalidParametersTemporaryFileProvider')]
    public function testItReturns422OnInvalidData(array $parameters, array $expectedErrorFields): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $fileKey = UploadTemporaryImageRequest::getFileKey();
        $uploadedFile = $this->createUploadedImage(
            mimeType: MimeTypeEnum::Png,
            originalName: 'my_product.png'
        );

        $this->requestMultipart(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            files: [$fileKey => $uploadedFile],
            parameters: $parameters
        );

        $this->assertResponseIsUnprocessable();
        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: $expectedErrorFields);
    }

    public static function invalidParametersTemporaryFileProvider(): iterable
    {
        $contextKey = UploadTemporaryImageRequest::getContextKey();

        yield 'empty context' => [
            'parameters' => [],
            'expectedErrorFields' => [$contextKey],
        ];
        yield 'invalid context' => [
            'parameters' => [$contextKey => 'invalid'],
            'expectedErrorFields' => [$contextKey],
        ];
    }

    #[DataProvider('invalidParametersFileProvider')]
    public function testItReturns422OnInvalidFile(
        bool $withFile,
        MimeTypeEnum $mimeType,
        string $filename,
        int $width,
        int $height,
    ): void {
        $client = self::createClient();
        $this->loginAsAdmin();

        $fileKey = UploadTemporaryImageRequest::getFileKey();
        $contextKey = UploadTemporaryImageRequest::getContextKey();

        $file = null;
        if ($withFile) {
            $file = $mimeType->isImage()
                ? $this->createUploadedImage(
                    mimeType: $mimeType,
                    originalName: $filename,
                    width: $width,
                    height: $height
                )
                : $this->createUploadedFile(
                    mimeType: $mimeType,
                    originalName: $filename,
                    content: 'content'
                );
        }

        $this->requestMultipart(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            files: $withFile ? [$fileKey => $file] : [],
            parameters: [$contextKey => ContextEnum::ProductMain->value]
        );

        $this->assertResponseIsUnprocessable();
        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: [$fileKey]);
    }

    public static function invalidParametersFileProvider(): iterable
    {
        yield 'empty file' => [
            'withFile' => false,
            'mimeType' => MimeTypeEnum::Png,
            'filename' => 'my_product.png',
            'width' => 100,
            'height' => 100,
        ];
        yield 'invalid file min width and height' => [
            'withFile' => true,
            'mimeType' => MimeTypeEnum::Png,
            'filename' => 'invalid_file.png',
            'width' => 99,
            'height' => 99,
        ];
        yield 'invalid file max width and height' => [
            'withFile' => true,
            'mimeType' => MimeTypeEnum::Png,
            'filename' => 'invalid_file.png',
            'width' => 5001,
            'height' => 100, // 5001px is too large for memory
        ];
        yield 'invalid file mime type' => [
            'withFile' => true,
            'mimeType' => MimeTypeEnum::Unknown,
            'filename' => 'invalid_file.png',
            'width' => 100,
            'height' => 100,
        ];
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }

    private function getReadRepository(): TemporaryImageReadRepositoryInterface
    {
        return self::getContainer()->get(TemporaryImageReadRepositoryInterface::class);
    }
}
