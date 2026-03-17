<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Product;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Service\Product\ProductValidator;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\ProductMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProductValidatorTest extends TestCase
{
    private ProductReadRepositoryInterface&MockObject $productReadRepository;
    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;
    private AttributeReadRepositoryInterface&MockObject $attributeReadRepository;
    private TemporaryImageReadRepositoryInterface&MockObject $temporaryImageReadRepository;

    public function setUp(): void
    {
        $this->productReadRepository = $this->createMock(ProductReadRepositoryInterface::class);
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->attributeReadRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->temporaryImageReadRepository = $this->createMock(TemporaryImageReadRepositoryInterface::class);
    }

    public function testItValidatesCreation(): void
    {
        $sku = $this->getSku();
        $categoryIds = $this->getCategoryIds();
        $attributeIds = $this->getAttributeIds();
        $temporaryImageUlids = $this->getTemporaryImageUlids();

        $this->givenSkuIsAvailable($sku);
        $this->givenCategoriesExist($categoryIds);
        $this->givenAttributesExist($attributeIds);
        $this->givenTemporaryImagesExist($temporaryImageUlids);

        $this->createValidator()->validateCreation(
            sku: $sku,
            categoryIds: $categoryIds,
            attributeIds: $attributeIds,
            temporaryImageUlids: $temporaryImageUlids
        );
    }

    public function testThrowsExceptionWhenProductExistsBySkuWhileCreating(): void
    {
        $sku = $this->getSku('EXISTING-SKU');

        $this->givenSkuIsTaken($sku);

        $this->checkCategoriesNeverCalled();
        $this->checkAttributesNeverCalled();
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(ProductAlreadyExistsException::class);

        $this->createValidator()->validateCreation(
            sku: $sku,
            categoryIds: $this->getCategoryIds(),
            attributeIds: $this->getAttributeIds(),
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfCategoriesNotFoundWhileCreating(): void
    {
        $sku = $this->getSku();
        $categoryIds = $this->getCategoryIds([123, 456, 789, 999]);

        $this->givenSkuIsAvailable($sku);
        $this->oneOfGivenCategoryDoesNotExists($categoryIds);
        $this->checkAttributesNeverCalled();
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(OneOfCategoriesNotFoundException::class);

        $this->createValidator()->validateCreation(
            sku: $sku,
            categoryIds: $categoryIds,
            attributeIds: $this->getAttributeIds(),
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfAttributesNotFoundWhileCreating(): void
    {
        $sku = $this->getSku();
        $categoryIds = $this->getCategoryIds();
        $attributeIds = $this->getAttributeIds([321, 654, 987, 999]);

        $this->givenSkuIsAvailable($sku);
        $this->givenCategoriesExist($categoryIds);
        $this->oneOfGivenAttributesDoesNotExists($attributeIds);
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(OneOfAttributesNotFoundException::class);

        $this->createValidator()->validateCreation(
            sku: $sku,
            categoryIds: $categoryIds,
            attributeIds: $attributeIds,
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfTemporaryImagesNotFoundWhileCreating(): void
    {
        $sku = $this->getSku();
        $categoryIds = $this->getCategoryIds();
        $attributeIds = $this->getAttributeIds();
        $temporaryImageUlids = $this->getTemporaryImageUlids([
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
            '01KKTVY7D6D7S1BCSBB3GQA8B6',
        ]);

        $this->givenSkuIsAvailable($sku);
        $this->givenCategoriesExist($categoryIds);
        $this->givenAttributesExist($attributeIds);
        $this->oneOfGivenTemporaryImagesDoesNotExists($temporaryImageUlids);

        $this->expectException(OneOfTemporaryImagesNotFoundException::class);

        $this->createValidator()->validateCreation(
            sku: $sku,
            categoryIds: $categoryIds,
            attributeIds: $attributeIds,
            temporaryImageUlids: $temporaryImageUlids
        );
    }

    public function testItValidatesUpdating(): void
    {
        $product = ProductMother::createWithData();
        $newSku = $this->getSku();

        self::assertFalse($product->getSku()->equals($newSku));

        $newCategoryIds = $this->getCategoryIds();
        $newAttributeIds = $this->getAttributeIds();
        $newTemporaryImageUlids = $this->getTemporaryImageUlids();

        $this->givenSkuIsAvailable($newSku);
        $this->givenCategoriesExist($newCategoryIds);
        $this->givenAttributesExist($newAttributeIds);
        $this->givenTemporaryImagesExist($newTemporaryImageUlids);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value(),
            newSku: $newSku,
            categoryIds: $newCategoryIds,
            attributeIds: $newAttributeIds,
            temporaryImageUlids: $newTemporaryImageUlids
        );
    }

    public function testThrowsExceptionWhenProductVersionDoesNotMatch(): void
    {
        $product = ProductMother::createWithData();

        $this->checkSkuNeverCalled();
        $this->checkCategoriesNeverCalled();
        $this->checkAttributesNeverCalled();
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(ConcurrencyException::class);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value() + 1,
            newSku: $this->getSku(),
            categoryIds: $this->getCategoryIds(),
            attributeIds: $this->getAttributeIds(),
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenProductExistsBySkuWhileUpdating(): void
    {
        $product = ProductMother::createWithData();
        $newSku = $this->getSku('EXISTING-SKU');

        $this->givenSkuIsTaken($newSku);
        $this->checkCategoriesNeverCalled();
        $this->checkAttributesNeverCalled();
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(ProductAlreadyExistsException::class);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value(),
            newSku: $newSku,
            categoryIds: $this->getCategoryIds(),
            attributeIds: $this->getAttributeIds(),
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfCategoriesNotFoundWhileUpdating(): void
    {
        $product = ProductMother::createWithData();
        $newSku = $this->getSku();
        $newCategoryIds = $this->getCategoryIds([123, 456, 789, 999]);

        $this->givenSkuIsAvailable($newSku);
        $this->oneOfGivenCategoryDoesNotExists($newCategoryIds);
        $this->checkAttributesNeverCalled();
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(OneOfCategoriesNotFoundException::class);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value(),
            newSku: $newSku,
            categoryIds: $newCategoryIds,
            attributeIds: $this->getAttributeIds(),
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfAttributesNotFoundWhileUpdating(): void
    {
        $product = ProductMother::createWithData();
        $newSku = $this->getSku();
        $newCategoryIds = $this->getCategoryIds();
        $newAttributeIds = $this->getAttributeIds([321, 654, 987, 999]);

        $this->givenSkuIsAvailable($newSku);
        $this->givenCategoriesExist($newCategoryIds);
        $this->oneOfGivenAttributesDoesNotExists($newAttributeIds);
        $this->checkTemporaryImagesNeverCalled();

        $this->expectException(OneOfAttributesNotFoundException::class);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value(),
            newSku: $newSku,
            categoryIds: $newCategoryIds,
            attributeIds: $newAttributeIds,
            temporaryImageUlids: $this->getTemporaryImageUlids()
        );
    }

    public function testThrowsExceptionWhenOneOfTemporaryImagesNotFoundWhileUpdating(): void
    {
        $product = ProductMother::createWithData();
        $newSku = $this->getSku();
        $newCategoryIds = $this->getCategoryIds();
        $newAttributeIds = $this->getAttributeIds();
        $newTemporaryImageUlids = $this->getTemporaryImageUlids([
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
            '01KKTVY7D6D7S1BCSBB3GQA8B6',
        ]);

        $this->givenSkuIsAvailable($newSku);
        $this->givenCategoriesExist($newCategoryIds);
        $this->givenAttributesExist($newAttributeIds);
        $this->oneOfGivenTemporaryImagesDoesNotExists($newTemporaryImageUlids);

        $this->expectException(OneOfTemporaryImagesNotFoundException::class);

        $this->createValidator()->validateUpdate(
            product: $product,
            version: $product->getVersion()->value(),
            newSku: $newSku,
            categoryIds: $newCategoryIds,
            attributeIds: $newAttributeIds,
            temporaryImageUlids: $newTemporaryImageUlids
        );
    }

    private function createValidator(): ProductValidator
    {
        return new ProductValidator(
            productReadRepository: $this->productReadRepository,
            categoryReadRepository: $this->categoryReadRepository,
            attributeReadRepository: $this->attributeReadRepository,
            temporaryImageReadRepository: $this->temporaryImageReadRepository
        );
    }

    private function getSku(string $sku = 'SKU-123-T'): Sku
    {
        return Sku::fromString($sku);
    }

    /**
     * @param ?int[] $ids
     *
     * @return CategoryId[]
     */
    private function getCategoryIds(?array $ids = null): array
    {
        $ids ??= [123, 456, 789];

        return array_map(static fn (int $id) => CategoryId::fromInt($id), $ids);
    }

    /**
     * @param ?int[] $ids
     *
     * @return AttributeId[]
     */
    private function getAttributeIds(?array $ids = null): array
    {
        $ids ??= [321, 654, 987];

        return array_map(static fn (int $id) => AttributeId::fromInt($id), $ids);
    }

    /**
     * @param ?string[] $ulids
     *
     * @return TemporaryImageUlid[]
     */
    private function getTemporaryImageUlids(?array $ulids = null): array
    {
        $ulids ??= [
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
        ];

        return array_map(static fn (string $ulid) => TemporaryImageUlid::fromString($ulid), $ulids);
    }

    private function givenSkuIsAvailable(Sku $sku): void
    {
        $this->expectSkuCheck($sku, false);
    }

    private function givenSkuIsTaken(Sku $sku): void
    {
        $this->expectSkuCheck($sku, true);
    }

    private function expectSkuCheck(Sku $sku, bool $exists): void
    {
        $this->productReadRepository->expects(self::once())
            ->method('existsBySku')
            ->with(self::equalTo($sku))
            ->willReturn($exists);
    }

    private function checkSkuNeverCalled(): void
    {
        $this->productReadRepository->expects(self::never())->method('existsBySku');
    }

    /**
     * @param CategoryId[] $categoryIds
     */
    private function givenCategoriesExist(array $categoryIds): void
    {
        $this->categoryReadRepository->expects(self::once())
            ->method('assertAllExistByIds')
            ->with(self::equalTo($categoryIds));
    }

    /**
     * @param CategoryId[] $categoryIds
     */
    private function oneOfGivenCategoryDoesNotExists(array $categoryIds): void
    {
        $this->categoryReadRepository->expects(self::once())
            ->method('assertAllExistByIds')
            ->with(self::equalTo($categoryIds))
            ->willThrowException(new OneOfCategoriesNotFoundException());
    }

    private function checkCategoriesNeverCalled(): void
    {
        $this->categoryReadRepository->expects(self::never())->method('assertAllExistByIds');
    }

    /**
     * @param AttributeId[] $attributeIds
     */
    private function givenAttributesExist(array $attributeIds): void
    {
        $this->attributeReadRepository->expects(self::once())
            ->method('assertAllExistByIds')
            ->with(self::equalTo($attributeIds));
    }

    /**
     * @param AttributeId[] $attributeIds
     */
    private function oneOfGivenAttributesDoesNotExists(array $attributeIds): void
    {
        $this->attributeReadRepository->expects(self::once())
            ->method('assertAllExistByIds')
            ->with(self::equalTo($attributeIds))
            ->willThrowException(new OneOfAttributesNotFoundException());
    }

    private function checkAttributesNeverCalled(): void
    {
        $this->attributeReadRepository->expects(self::never())->method('assertAllExistByIds');
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImageUlids
     */
    private function givenTemporaryImagesExist(array $temporaryImageUlids): void
    {
        $this->temporaryImageReadRepository->expects(self::once())
            ->method('assertAllExistByUlidAndContext')
            ->with(
                self::equalTo($temporaryImageUlids),
                self::equalTo(ContextEnum::ProductMain)
            );
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImageUlids
     */
    private function oneOfGivenTemporaryImagesDoesNotExists(array $temporaryImageUlids): void
    {
        $this->temporaryImageReadRepository->expects(self::once())
            ->method('assertAllExistByUlidAndContext')
            ->with(
                self::equalTo($temporaryImageUlids),
                self::equalTo(ContextEnum::ProductMain)
            )
            ->willThrowException(new OneOfTemporaryImagesNotFoundException());
    }

    private function checkTemporaryImagesNeverCalled(): void
    {
        $this->temporaryImageReadRepository->expects(self::never())->method('assertAllExistByUlidAndContext');
    }
}
