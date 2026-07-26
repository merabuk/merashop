<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UpdateProduct;

use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductHandler;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\Service\Product\ProductApplicationFactoryInterface;
use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum as PriceTypeEnum;
use App\Catalog\Domain\Event\ProductImagesRemovedDomainEvent;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\Product\ProductImagesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\Service\Product\ProductValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\ProductPriceMother;
use App\Tests\Catalog\Support\Traits\ProductHelperTrait;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateProductHandlerTest extends BaseUnitTest
{
    use ProductHelperTrait;

    private ProductReadRepositoryInterface&MockObject $readRepository;
    private ProductValidatorInterface&MockObject $productValidator;
    private ProductApplicationFactoryInterface&MockObject $productFactory;
    private ProductMediaManagerInterface&MockObject $productMediaManager;
    private ProductWriteRepositoryInterface&MockObject $writeRepository;
    private MessageBusInterface&MockObject $messageBus;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(ProductReadRepositoryInterface::class);
        $this->productValidator = $this->createMock(ProductValidatorInterface::class);
        $this->productFactory = $this->createMock(ProductApplicationFactoryInterface::class);
        $this->productMediaManager = $this->createMock(ProductMediaManagerInterface::class);
        $this->writeRepository = $this->createMock(ProductWriteRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
    }

    public function testHandleSuccess(): void
    {
        $oldImage = ProductImageMother::createWithData(isMain: true);
        $product = ProductMother::createWithData(
            sku: 'OLD-TEST-SKU',
            status: StatusEnum::Draft,
            translations: ['en' => ['name' => 'Old Product']],
            prices: [
                ProductPriceMother::createWithData(
                    currency: CurrencyEnum::UAH,
                    type: PriceTypeEnum::Regular,
                ),
            ],
            attributeValues: [
                ProductAttributeValueMother::createWithData(
                    attributeId: 987,
                    attributeType: AttributeTypeEnum::Integer
                ),
            ],
            images: [$oldImage],
            withFakeIds: true
        );
        $productForUpdate = ProductMother::createWithData(
            createdByUlid: '01KM2VXDT53FFTM8A520WFDK9T',
            updatedByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            withFakeIds: true
        );
        $command = $this->fillAndGetUpdateCommand($productForUpdate);

        $expectedCategoryIds = self::mapToCategoryIds($command->categoryIds);
        $expectedAttributeIds = self::mapToAttributeIds($command->attributeValues);
        $temporaryImageUlids = self::mapToTemporaryImagesUlids($command->images, $product->getImages());
        $productImagesUlidsForDelete = self::mapToProductImagesUlidsForDelete($command->images, $product->getImages());

        $this->expectProductFound(product: $product, productId: $command->id);
        $this->expectMapCategoryIds(input: $command->categoryIds, result: $expectedCategoryIds);
        $this->expectMapAttributeIds(input: $command->attributeValues, result: $expectedAttributeIds);
        $this->expectMapTemporaryImagesUlids(
            images: $command->images,
            collection: $product->getImages(),
            result: $temporaryImageUlids
        );
        $this->expectMapProductImagesUlidsForDelete(
            images: $command->images,
            collection: $product->getImages(),
            result: $productImagesUlidsForDelete
        );
        $this->expectPassValidation(
            product: $product,
            command: $command,
            categoryIds: $expectedCategoryIds,
            attributeIds: $expectedAttributeIds,
            temporaryImages: $temporaryImageUlids,
            productImagesForDelete: $productImagesUlidsForDelete,
        );
        $this->expectFactoryUpdateProduct(
            product: $product,
            command: $command,
            expectedState: $productForUpdate,
        );
        $this->exceptSyncProductImages(
            product: $product,
            images: $command->images,
            result: [$oldImage->getPath()],
        );
        $this->expectSaveProduct(expectedProduct: $product);
        $this->expectDeleteTemporaryImages(temporaryImages: $temporaryImageUlids);
        $this->expectDispatchEvent();

        $this->createHandler()($command);
    }

    private function createHandler(): UpdateProductHandler
    {
        return new UpdateProductHandler(
            readRepository: $this->readRepository,
            productValidator: $this->productValidator,
            productFactory: $this->productFactory,
            productMediaManager: $this->productMediaManager,
            writeRepository: $this->writeRepository,
            eventBus: $this->messageBus,
        );
    }

    public function testThrowsExceptionWhenProductNotFound(): void
    {
        $product = ProductMother::createWithData(
            updatedByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            withFakeIds: true
        );
        $command = $this->fillAndGetUpdateCommand($product);

        $this->expectsProductNotFound($command->id);
        $this->mapCategoryIdsNeverCalled();
        $this->mapAttributeIdsNeverCalled();
        $this->mapTemporaryImagesUlidsNeverCalled();
        $this->mapProductImagesUlidsForDeleteNeverCalled();
        $this->validationCheckNeverCalled();
        $this->updateProductFactoryNeverCalled();
        $this->syncProductImagesNeverCalled();
        $this->saveProductNeverCalled();
        $this->deleteTemporaryImagesNeverCalled();
        $this->eventBusNeverCalled();

        $this->expectException(ProductNotFoundException::class);

        $this->createHandler()($command);
    }

    #[DataProvider('provideValidationExceptions')]
    public function testThrowsExceptionWhenValidationFails(
        string $exceptionClass,
        ?int $version = null,
        ?string $sku = null,
        bool $deleteAllImages = false,
    ): void {
        $product = ProductMother::createWithData(
            sku: 'OLD-TEST-SKU',
            updatedByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            withFakeIds: true
        );
        $command = $this->fillAndGetUpdateCommand(
            product: $product,
            version: $version,
            sku: $sku,
            images: $deleteAllImages ? [] : null,
        );

        $expectedCategoryIds = self::mapToCategoryIds($command->categoryIds);
        $expectedAttributeIds = self::mapToAttributeIds($command->attributeValues);
        $temporaryImageUlids = self::mapToTemporaryImagesUlids($command->images, $product->getImages());
        $productImagesUlidsForDelete = self::mapToProductImagesUlidsForDelete($command->images, $product->getImages());

        $this->expectProductFound(product: $product, productId: $command->id);
        $this->expectMapCategoryIds(input: $command->categoryIds, result: $expectedCategoryIds);
        $this->expectMapAttributeIds(input: $command->attributeValues, result: $expectedAttributeIds);
        $this->expectMapTemporaryImagesUlids(
            images: $command->images,
            collection: $product->getImages(),
            result: $temporaryImageUlids
        );
        $this->expectMapProductImagesUlidsForDelete(
            images: $command->images,
            collection: $product->getImages(),
            result: $productImagesUlidsForDelete
        );
        $this->expectValidationCheck(
            product: $product,
            command: $command,
            categoryIds: $expectedCategoryIds,
            attributeIds: $expectedAttributeIds,
            temporaryImages: $temporaryImageUlids,
            productImagesForDelete: $productImagesUlidsForDelete,
            exceptionClass: $exceptionClass,
        );

        $this->updateProductFactoryNeverCalled();
        $this->syncProductImagesNeverCalled();
        $this->saveProductNeverCalled();
        $this->deleteTemporaryImagesNeverCalled();

        $this->expectException($exceptionClass);

        $this->createHandler()($command);
    }

    public static function provideValidationExceptions(): iterable
    {
        yield 'invalid version' => [
            'exceptionClass' => ConcurrencyException::class,
            'version' => 2,
        ];
        yield 'sku already exists' => [
            'exceptionClass' => ProductAlreadyExistsException::class,
            'sku' => 'EXISTING-SKU',
        ];
        yield 'category ids not found' => [
            'exceptionClass' => OneOfCategoriesNotFoundException::class,
        ];
        yield 'attribute ids not found' => [
            'exceptionClass' => OneOfAttributesNotFoundException::class,
        ];
        yield 'temporary image ulids not found' => [
            'exceptionClass' => OneOfTemporaryImagesNotFoundException::class,
        ];
        yield 'cannot delete all images for active product' => [
            'exceptionClass' => ProductImagesEmptyException::class,
            'deleteAllImages' => true,
        ];
    }

    private function expectProductFound(Product $product, int $productId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (ProductId $id) => $id->value() === $productId))
            ->willReturn($product);
    }

    public function expectsProductNotFound(int $productId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (ProductId $id) => $id->value() === $productId))
            ->willThrowException(ProductNotFoundException::withId($productId));
    }

    /**
     * @param int[]        $input
     * @param CategoryId[] $result
     */
    private function expectMapCategoryIds(array $input, array $result): void
    {
        $this->productFactory->expects(self::once())
            ->method('mapCategoryIds')
            ->with(self::equalTo($input))
            ->willReturn($result);
    }

    private function mapCategoryIdsNeverCalled(): void
    {
        $this->productFactory->expects(self::never())->method('mapCategoryIds');
    }

    /**
     * @param ProductAttributeValueData[] $input
     * @param AttributeId[]               $result
     */
    private function expectMapAttributeIds(array $input, array $result): void
    {
        $this->productFactory->expects(self::once())
            ->method('mapAttributeIds')
            ->with(self::equalTo($input))
            ->willReturn($result);
    }

    private function mapAttributeIdsNeverCalled(): void
    {
        $this->productFactory->expects(self::never())->method('mapAttributeIds');
    }

    /**
     * @param string[]             $images
     * @param TemporaryImageUlid[] $result
     */
    private function expectMapTemporaryImagesUlids(array $images, ImageCollection $collection, array $result): void
    {
        $this->productMediaManager->expects(self::once())
            ->method('mapTemporaryImagesUlids')
            ->with(self::equalTo($images), self::equalTo($collection))
            ->willReturn($result);
    }

    private function mapTemporaryImagesUlidsNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('mapTemporaryImagesUlids');
    }

    /**
     * @param string[]           $images
     * @param ProductImageUlid[] $result
     */
    private function expectMapProductImagesUlidsForDelete(
        array $images,
        ImageCollection $collection,
        array $result,
    ): void {
        $this->productMediaManager->expects(self::once())
            ->method('mapProductImagesUlidsForDelete')
            ->with(self::equalTo($images), self::equalTo($collection))
            ->willReturn($result);
    }

    private function mapProductImagesUlidsForDeleteNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('mapProductImagesUlidsForDelete');
    }

    private function expectPassValidation(
        Product $product,
        UpdateProductCommand $command,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImages,
        array $productImagesForDelete,
    ): void {
        $this->expectValidationCheck(
            product: $product,
            command: $command,
            categoryIds: $categoryIds,
            attributeIds: $attributeIds,
            temporaryImages: $temporaryImages,
            productImagesForDelete: $productImagesForDelete,
        );
    }

    private function expectValidationCheck(
        Product $product,
        UpdateProductCommand $command,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImages,
        array $productImagesForDelete,
        ?string $exceptionClass = null,
    ): void {
        $invokeContext = $this->productValidator->expects(self::once())
            ->method('validateUpdate')
            ->with(
                self::equalTo($product),
                self::equalTo($command->version),
                self::callback(fn (Sku $sku) => $sku->value() === $command->sku),
                self::equalTo($categoryIds),
                self::equalTo($attributeIds),
                self::equalTo($temporaryImages),
                self::equalTo($productImagesForDelete),
            );

        if ($exceptionClass) {
            $exception = ProductAlreadyExistsException::class === $exceptionClass
                ? ProductAlreadyExistsException::becauseSkuAlreadyExists($command->sku)
                : new $exceptionClass();

            $invokeContext->willThrowException($exception);
        }
    }

    private function validationCheckNeverCalled(): void
    {
        $this->productValidator->expects(self::never())->method('validateUpdate');
    }

    private function expectFactoryUpdateProduct(
        Product $product,
        UpdateProductCommand $command,
        Product $expectedState,
    ): void {
        $this->productFactory->expects(self::once())
            ->method('updateFromCommand')
            ->with(
                self::equalTo($product),
                self::equalTo($command),
            )->willReturnCallback(function (Product $updatedProduct, UpdateProductCommand $cmd) use ($expectedState) {
                $updatedProduct->update(
                    sku: $expectedState->getSku(),
                    status: $expectedState->getStatus(),
                    translations: $expectedState->getTranslations(),
                    updatedBy: $expectedState->getUpdatedBy(),
                    prices: $expectedState->getPrices(),
                    categoryIds: $expectedState->getCategoryIds(),
                    attributeValues: $expectedState->getAttributeValues(),
                );
            });
    }

    private function updateProductFactoryNeverCalled(): void
    {
        $this->productFactory->expects(self::never())->method('updateFromCommand');
    }

    /**
     * @param string[]           $images
     * @param RelativeFilePath[] $result
     */
    private function exceptSyncProductImages(
        Product $product,
        array $images,
        array $result,
    ): void {
        $this->productMediaManager->expects(self::once())
            ->method('syncProductImages')
            ->with(
                self::equalTo($product),
                self::equalTo($images),
            )
            ->willReturn($result);
    }

    private function syncProductImagesNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('syncProductImages');
    }

    private function expectSaveProduct(Product $expectedProduct): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Product $updatedProduct) use ($expectedProduct): bool {
                self::assertTrue($expectedProduct->getSku()->equals($updatedProduct->getSku()));
                self::assertTrue($expectedProduct->getStatus()->equals($updatedProduct->getStatus()));
                self::assertTrue($expectedProduct->getTranslations()->equals($updatedProduct->getTranslations()));
                self::assertTrue($expectedProduct->getPrices()->equals($updatedProduct->getPrices()));
                self::assertTrue($expectedProduct->getCategoryIds()->equals($updatedProduct->getCategoryIds()));
                self::assertTrue($expectedProduct->getAttributeValues()->equals($updatedProduct->getAttributeValues()));
                self::assertNotNull($expectedProduct->getUpdatedBy());
                self::assertNotNull($updatedProduct->getUpdatedBy());
                self::assertTrue($expectedProduct->getUpdatedBy()->equals($updatedProduct->getUpdatedBy()));

                return true;
            }))
            ->willReturnArgument(0);
    }

    private function saveProductNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImages
     */
    private function expectDeleteTemporaryImages(array $temporaryImages): void
    {
        $this->productMediaManager->expects(self::once())
            ->method('deleteTemporaryImages')
            ->with(self::equalTo($temporaryImages));
    }

    private function deleteTemporaryImagesNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('deleteTemporaryImages');
    }

    private function expectDispatchEvent(): void
    {
        $this->messageBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $event) {
                self::assertInstanceOf(ProductImagesRemovedDomainEvent::class, $event);
                self::assertNotEmpty($event->productImagePaths);

                return true;
            }))
            ->willReturn(new Envelope(new stdClass()));
    }

    private function eventBusNeverCalled(): void
    {
        $this->messageBus->expects(self::never())->method('dispatch');
    }
}
