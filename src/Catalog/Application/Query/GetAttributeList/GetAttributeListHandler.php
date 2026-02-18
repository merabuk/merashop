<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttributeList;

use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Query->value)]
readonly class GetAttributeListHandler implements QueryHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
    ) {
    }

    public function __invoke(GetAttributeListQuery $query): PaginatedResult
    {
        return $this->readRepository->paginate($query->criteria);
    }
}
