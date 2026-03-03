<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AttributeReadRepositoryTest extends KernelTestCase
{
    use AttributeFactoryTrait;

    private AttributeReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(AttributeReadRepositoryInterface::class);
    }

    public function testFindByIdSuccess(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $id = $attribute->getId();

        $found = $this->repository->findById($id);

        self::assertNotNull($found);
        self::assertSame($attribute->getUlid()->value(), $found->getUlid()->value());
        self::assertSame($attribute->getCode()->value(), $found->getCode()->value());
        self::assertCount(count($attribute->getTranslations()->toArray()), $found->getTranslations()->toArray());
    }

    /**
     * @throws InvalidAttributeIdException
     */
    public function testGetByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(AttributeNotFoundException::class);

        $this->repository->getById(Id::fromInt(999999));
    }

    public function testFindByUlid(): void
    {
        $attribute = $this->getAttributeFixture()->create();
        $ulid = $attribute->getUlid();

        $found = $this->repository->findByUlid($ulid);

        self::assertNotNull($found);
        self::assertSame($attribute->getId()->value(), $found->getId()->value());
    }

    /**
     * @throws InvalidAttributeCodeException
     */
    public function testExistsByCode(): void
    {
        $code = Code::fromString('unique_test_code');

        self::assertFalse($this->repository->existsByCode($code));

        $this->getAttributeFixture()->create(code: $code->value());

        self::assertTrue($this->repository->existsByCode($code));
    }

    /**
     * @throws OneOfAttributesNotFoundException
     */
    public function testAssertAllExistByIdsSuccess(): void
    {
        $attr1 = $this->getAttributeFixture()->create();
        $attr2 = $this->getAttributeFixture()->create();

        $this->repository->assertAllExistByIds([$attr1->getId(), $attr2->getId()]);
        self::assertTrue(true);
    }

    /**
     * @throws InvalidAttributeIdException
     */
    public function testAssertAllExistByIdsThrowsExceptionOnFailure(): void
    {
        $attr1 = $this->getAttributeFixture()->create();
        $invalidId = Id::fromInt(999999);

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
