<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Event\ProductImagesRemovedDomainEvent;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::CatalogInternal->value)]
readonly class ProductImagesRemovedHandler implements EventHandlerInterface
{
    public function __construct(
        private ProductMediaManagerInterface $productMediaManager,
    ) {
    }

    /**
     * @throws InvalidRelativePathException
     * @throws FileStorageException
     */
    public function __invoke(ProductImagesRemovedDomainEvent $event): void
    {
        $productImagePaths = array_map(
            static fn (string $path) => RelativeFilePath::fromString($path),
            $event->productImagePaths
        );

        $this->productMediaManager->deleteProductImages($productImagePaths);
    }
}
