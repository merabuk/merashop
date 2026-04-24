<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttributeItem;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeUlidException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Query\QueryHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Query->value)]
readonly class GetAttributeItemHandler implements QueryHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @throws AttributeNotFoundException
     * @throws InvalidAttributeUlidException
     */
    public function __invoke(GetAttributeItemQuery $query): Attribute
    {
        return $this->readRepository->getByUlid(Ulid::fromString($query->ulid));
    }
}
