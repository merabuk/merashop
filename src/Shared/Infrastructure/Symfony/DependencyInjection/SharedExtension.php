<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\DependencyInjection;

use App\Shared\Domain\Helpers\TypeCastingTrait;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

final class SharedExtension extends Extension implements PrependExtensionInterface
{
    use TypeCastingTrait;

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            container: $container,
            locator: new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->registerModuleTranslations($container);
    }

    /**
     * Automatically finds all "translations" folders in modules and registers them in FrameworkBundle.
     */
    private function registerModuleTranslations(ContainerBuilder $container): void
    {
        $projectDir = self::castToString(value: $container->getParameter('kernel.project_dir'));
        $srcDir = $projectDir.'/src';

        if (!is_dir($srcDir)) {
            return;
        }

        $finder = new Finder();
        $finder->directories()->in($srcDir)->name('translations');

        $paths = [];
        foreach ($finder as $dir) {
            $paths[] = $dir->getRealPath();
        }

        if (empty($paths)) {
            return;
        }

        $container->prependExtensionConfig('framework', [
            'translator' => [
                'paths' => $paths,
            ],
        ]);
    }
}
