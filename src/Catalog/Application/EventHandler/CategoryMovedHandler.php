<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Event\CategoryMovedDomainEvent;
use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::CatalogInternal->value)]
readonly class CategoryMovedHandler implements EventHandlerInterface
{
    public function __construct(
        private CategoryWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws InvalidCategoryPathException
     */
    public function __invoke(CategoryMovedDomainEvent $event): void
    {
        $this->writeRepository->replaceOldPathOnNew(
            oldPath: Path::fromString($event->oldPath),
            newPath: Path::fromString($event->newPath)
        );
    }
}
