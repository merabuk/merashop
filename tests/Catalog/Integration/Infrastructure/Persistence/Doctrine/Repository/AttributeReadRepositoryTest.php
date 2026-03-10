<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AttributeReadRepositoryTest extends KernelTestCase
{
    use AttributeFactoryTrait;
    use CatalogEntityManagerTrait;
    use ValueObjectAssertionTrait;

    private AttributeReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(AttributeReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($attribute->getId());

        self::assertNotNull($found);
        self::assertTrue($attribute->getUlid()->equals($found->getUlid()));
        self::assertTrue($attribute->getCode()->equals($found->getCode()));
        self::assertTrue($attribute->getType()->equals($found->getType()));
        self::assertCount($attribute->getTranslations()->count(), $found->getTranslations());
        foreach ($attribute->getTranslations() as $locale => $translation) {
            $foundTranslation = $found->getTranslations()->get($locale);
            self::assertNotNull($foundTranslation);
            self::assertSame($translation->name, $foundTranslation->name);
        }
        self::assertTrue($attribute->getVersion()->equals($found->getVersion()));
        self::assertTrue($attribute->getCreatedBy()->equals($found->getCreatedBy()));
        $this->assertVoEqualsOrNull($attribute->getUpdatedBy(), $found->getUpdatedBy());
    }

    public function testGetByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(AttributeNotFoundException::class);

        $this->repository->getById(Id::fromInt(1));
    }

    public function testFindByUlid(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($attribute->getUlid());

        self::assertNotNull($found);
        self::assertSame($attribute->getId()->value(), $found->getId()->value());
    }

    public function testExistsByCode(): void
    {
        $code = Code::fromString('unique_test_code');

        self::assertFalse($this->repository->existsByCode($code));

        $this->getAttributeFixture()->create(code: $code->value());

        self::assertTrue($this->repository->existsByCode($code));
    }

    public function testAssertAllExistByIds(): void
    {
        $attr1 = $this->getAttributeFixture()->create();
        $attr2 = $this->getAttributeFixture()->create();

        $this->repository->assertAllExistByIds([$attr1->getId(), $attr2->getId()]);
        self::assertTrue(true);
    }

    public function testAssertAllExistByIdsThrowsExceptionOnFailure(): void
    {
        $attr1 = $this->getAttributeFixture()->create();
        $invalidId = Id::fromInt(1);

        $this->expectException(OneOfAttributesNotFoundException::class);

        $this->repository->assertAllExistByIds([$attr1->getId(), $invalidId]);
    }

    public function testPaginateWithSearch(): void
    {
        $this->getAttributeFixture()->create(code: 'color_red');
        $this->getAttributeFixture()->create(code: 'color_blue');
        $this->getAttributeFixture()->create(code: 'size_xl');

        $criteria = new Criteria(
            cursor: new Cursor(lastSeenIdentifier: null, perPage: Cursor::DEFAULT_PER_PAGE),
            filters: new Filters(['search' => 'color']),
            sort: new Sort(field: 'code', direction: Sort::ASC)
        );

        $result = $this->repository->paginate($criteria);

        self::assertSame(2, $result->totalCount);
        self::assertCount(2, $result->items);
        self::assertSame('color_blue', $result->items[0]->getCode()->value());
    }
}
