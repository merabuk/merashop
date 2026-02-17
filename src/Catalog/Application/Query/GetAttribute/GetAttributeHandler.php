<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Query\QueryHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Query->value)]
readonly class GetAttributeHandler implements QueryHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @throws AttributeNotFoundException
     * @throws InvalidAttributeIdException
     */
    public function __invoke(GetAttributeQuery $query): Attribute
    {
        return $this->readRepository->getById(Id::fromInt($query->id));
    }
}
