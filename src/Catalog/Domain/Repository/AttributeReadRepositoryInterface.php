<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;

interface AttributeReadRepositoryInterface
{
    /**
     * @throws AttributeNotFoundException
     */
    public function getById(Id $id, bool $withTranslations = true): Attribute;

    public function findById(Id $id, bool $withTranslations = true): ?Attribute;

    public function findByUlid(Ulid $ulid): ?Attribute;

    public function existsByCode(Code $code): bool;

    /**
     * @param Id[] $ids
     *
     * @throws OneOfAttributesNotFoundException
     */
    public function assertAllExistByIds(array $ids): void;

    /**
     * @return PaginatedResult<Attribute>
     */
    public function paginate(Criteria $criteria): PaginatedResult;
}
