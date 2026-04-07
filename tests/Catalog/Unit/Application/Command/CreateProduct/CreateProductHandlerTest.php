<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateProduct;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\CreateProduct\CreateProductHandler;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\Service\Product\ProductApplicationFactoryInterface;
use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\Service\Product\ProductValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\Traits\ProductHelperTrait;
use App\Tests\Shared\Support\Traits\UlidGenerationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    use ProductHelperTrait;
    use UlidGenerationTrait;

    private ProductValidatorInterface&MockObject $productValidator;
    private ProductApplicationFactoryInterface&MockObject $productFactory;
    private ProductMediaManagerInterface&MockObject $productMediaManager;
    private ProductWriteRepositoryInterface&MockObject $writeRepository;

    public function setUp(): void
    {
        $this->setUlidGenerator();
        $this->productValidator = $this->createMock(ProductValidatorInterface::class);
        $this->productFactory = $this->createMock(ProductApplicationFactoryInterface::class);
        $this->productMediaManager = $this->createMock(ProductMediaManagerInterface::class);
        $this->writeRepository = $this->createMock(ProductWriteRepositoryInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $product = ProductMother::createWithData(withFakeIds: true);
        $command = $this->fillAndGetCreateCommand($product);

        $expectedCategoryIds = $this->getExpectedCategoryIds($product);
        $expectedAttributeIds = $this->getExpectedAttributeIds($product);
        $expectedTemporaryImageUlids = $this->getExpectedTemporaryImageUlids($product);

        $this->expectMapCategoryIds($command->categoryIds, $expectedCategoryIds);
        $this->expectMapAttributeIds($command->attributeValues, $expectedAttributeIds);
        $this->exceptMapTemporaryImageUlids($command->images, $expectedTemporaryImageUlids);
        $this->expectPassValidation(
            sku: $product->getSku(),
            categoryIds: $expectedCategoryIds,
            attributeIds: $expectedAttributeIds,
            temporaryImageUlids: $expectedTemporaryImageUlids
        );
        $this->expectGenerateUlid($product->getUlid()->value());
        $this->expectFactoryCreateProduct($command, $product);
        $this->expectActivateImagesForProduct($product, $command->images);
        $this->expectSaveProduct($product);
        $this->expectTemporaryImagesDeletion($expectedTemporaryImageUlids);

        $result = $this->creteHandler()($command);

        self::assertSame($product->getId()->value(), $result);
    }

    #[DataProvider('provideValidationExceptions')]
    public function testThrowsExceptionWhenValidationFails(string $exceptionClass): void
    {
        $product = ProductMother::createWithData();
        $command = $this->fillAndGetCreateCommand($product);

        $expectedCategoryIds = $this->getExpectedCategoryIds($product);
        $expectedAttributeIds = $this->getExpectedAttributeIds($product);
        $expectedTemporaryImageUlids = $this->getExpectedTemporaryImageUlids($product);

        $this->expectMapCategoryIds($command->categoryIds, $expectedCategoryIds);
        $this->expectMapAttributeIds($command->attributeValues, $expectedAttributeIds);
        $this->exceptMapTemporaryImageUlids($command->images, $expectedTemporaryImageUlids);
        $this->expectValidationFailsWithException(
            sku: $product->getSku(),
            categoryIds: $expectedCategoryIds,
            attributeIds: $expectedAttributeIds,
            temporaryImageUlids: $expectedTemporaryImageUlids,
            exceptionClass: $exceptionClass
        );
        $this->generateUlidNeverCalled();
        $this->factoryCreateProductNeverCalled();
        $this->activateImagesForProductNeverCalled();
        $this->saveProductNeverCalled();
        $this->temporaryImagesDeletionNeverCalled();

        $this->expectException($exceptionClass);

        $this->creteHandler()($command);
    }

    public static function provideValidationExceptions(): iterable
    {
        yield 'sku already exists' => [
            'exceptionClass' => ProductAlreadyExistsException::class,
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
    }

    private function creteHandler(): CreateProductHandler
    {
        return new CreateProductHandler(
            productValidator: $this->productValidator,
            ulidGenerator: $this->ulidGenerator,
            productFactory: $this->productFactory,
            productMediaManager: $this->productMediaManager,
            writeRepository: $this->writeRepository
        );
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

    /**
     * @param string[]             $input
     * @param TemporaryImageUlid[] $result
     */
    private function exceptMapTemporaryImageUlids(array $input, array $result): void
    {
        $this->productMediaManager->expects(self::once())
            ->method('mapTemporaryImagesUlids')
            ->with(self::equalTo($input))
            ->willReturn($result);
    }

    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImageUlids
     */
    private function expectPassValidation(
        Sku $sku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImageUlids,
    ): void {
        $this->productValidator->expects(self::once())
            ->method('validateCreation')
            ->with(
                self::equalTo($sku),
                self::equalTo($categoryIds),
                self::equalTo($attributeIds),
                self::equalTo($temporaryImageUlids),
            );
    }

    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImageUlids
     */
    private function expectValidationFailsWithException(
        Sku $sku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImageUlids,
        string $exceptionClass,
    ): void {
        if (ProductAlreadyExistsException::class === $exceptionClass) {
            $exception = ProductAlreadyExistsException::becauseSkuAlreadyExists($sku->value());
        } else {
            $exception = new $exceptionClass();
        }

        $this->productValidator->expects(self::once())
            ->method('validateCreation')
            ->with(
                self::equalTo($sku),
                self::equalTo($categoryIds),
                self::equalTo($attributeIds),
                self::equalTo($temporaryImageUlids),
            )
            ->willThrowException($exception);
    }

    private function expectFactoryCreateProduct(CreateProductCommand $command, Product $product): void
    {
        $this->productFactory->expects(self::once())
            ->method('createFromCommand')
            ->with(
                self::equalTo($command),
                self::equalTo($product->getUlid()->value()),
            )
            ->willReturn($product);
    }

    private function factoryCreateProductNeverCalled(): void
    {
        $this->productFactory->expects(self::never())->method('createFromCommand');
    }

    /**
     * @param string[] $temporaryImageUlids
     */
    private function expectActivateImagesForProduct(Product $product, array $temporaryImageUlids): void
    {
        $this->productMediaManager->expects(self::once())
            ->method('activateImagesForProduct')
            ->with(
                self::equalTo($product),
                self::equalTo($temporaryImageUlids),
            );
    }

    private function activateImagesForProductNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('activateImagesForProduct');
    }

    private function expectSaveProduct(Product $product): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Product $updatedProduct) use ($product): bool {
                self::assertTrue($product->getUlid()->equals($updatedProduct->getUlid()));
                self::assertTrue($product->getSku()->equals($updatedProduct->getSku()));
                self::assertTrue($product->getStatus()->equals($updatedProduct->getStatus()));
                self::assertTrue($product->getTranslations()->equals($updatedProduct->getTranslations()));
                self::assertTrue($product->getVersion()->equals($updatedProduct->getVersion()));
                self::assertTrue($product->getCreatedBy()->equals($updatedProduct->getCreatedBy()));
                self::assertTrue($product->getPrices()->equals($updatedProduct->getPrices()));
                self::assertTrue($product->getCategoryIds()->equals($updatedProduct->getCategoryIds()));
                self::assertTrue($product->getAttributeValues()->equals($updatedProduct->getAttributeValues()));
                self::assertTrue($product->getImages()->equals($updatedProduct->getImages()));
                self::assertNull($updatedProduct->getUpdatedBy());

                return true;
            }))
            ->willReturn($product);
    }

    private function saveProductNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImageUlids
     */
    private function expectTemporaryImagesDeletion(array $temporaryImageUlids): void
    {
        $this->productMediaManager->expects(self::once())
            ->method('deleteTemporaryImages')
            ->with(self::equalTo($temporaryImageUlids));
    }

    private function temporaryImagesDeletionNeverCalled(): void
    {
        $this->productMediaManager->expects(self::never())->method('deleteTemporaryImages');
    }

    /**
     * @return CategoryId[]
     */
    private function getExpectedCategoryIds(Product $product): array
    {
        return $product->getCategoryIds()->all();
    }

    /**
     * @return AttributeId[]
     */
    private function getExpectedAttributeIds(Product $product): array
    {
        return array_map(fn (ProductAttributeValue $pav) => $pav->getAttributeId(), $product->getAttributeValues()->all());
    }

    /**
     * @return TemporaryImageUlid[]
     */
    private function getExpectedTemporaryImageUlids(Product $product): array
    {
        return array_map(fn (ProductImage $pi) => $pi->getUlid(), $product->getImages()->all());
    }
}
