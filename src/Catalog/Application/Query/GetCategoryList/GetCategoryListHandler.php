<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetCategoryList;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Query->value)]
readonly class GetCategoryListHandler implements QueryHandlerInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @return PaginatedResult<Category>
     */
    public function __invoke(GetCategoryListQuery $query): PaginatedResult
    {
        return $this->readRepository->paginate($query->criteria);
    }
}
