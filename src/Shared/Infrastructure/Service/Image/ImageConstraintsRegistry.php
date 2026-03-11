<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service\Image;

use App\Shared\Domain\Service\Image\ImageConstraintsProviderInterface;
use App\Shared\Domain\Service\Image\ImageConstraintsRegistryInterface;
use App\Shared\Domain\ValueObject\File\ImageConstraints;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class ImageConstraintsRegistry implements ImageConstraintsRegistryInterface
{
    public function __construct(
        #[AutowireLocator(
            services: 'shared.image_constraints_provider',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
    ) {
    }

    public function getConstraints(string $context): ImageConstraints
    {
        try {
            if (!$this->providers->has($context)) {
                throw new RuntimeException(sprintf('Image constraints provider "%s" not found', $context));
            }

            $provider = $this->providers->get($context);

            if ($provider instanceof ImageConstraintsProviderInterface) {
                return $provider->getConstraints();
            }

            throw new RuntimeException(sprintf('Image constraints provider "%s" is not an instance of %s', $context, ImageConstraintsProviderInterface::class));
        } catch (ContainerExceptionInterface|RuntimeException) {
            return $this->getDefaultImageConstraints();
        }
    }

    private function getDefaultImageConstraints(): ImageConstraints
    {
        return new ImageConstraints(
            maxSize: 5 * 1024 * 1024, // 5MB,
            allowedMimeTypes: ['image/jpeg', 'image/png'],
            maxWidth: 5000,
            maxHeight: 5000
        );
    }
}
