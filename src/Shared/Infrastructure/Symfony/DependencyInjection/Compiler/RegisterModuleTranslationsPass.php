<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

final class RegisterModuleTranslationsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        $projectDir = $container->getParameter('kernel.project_dir');
        $srcDir = $projectDir.'/src';

        if (!is_dir($srcDir)) {
            return;
        }

        $finder = new Finder();
        $finder->directories()->in($srcDir)->name('translations');

        $modulePaths = [];
        foreach ($finder as $dir) {
            $modulePaths[] = $dir->getRealPath();
        }

        if (empty($modulePaths)) {
            return;
        }

        /**
         * translator.default service arguments:
         * 0: ContainerInterface
         * 1: Formatter
         * 2: Default Locale
         * 3: Loader IDs (array)
         * 4: Options (array)
         */
        $definition = $container->getDefinition('translator.default');
        $options = $definition->getArgument(4);

        $scanned = array_flip($options['scanned_directories'] ?? []);
        foreach ($modulePaths as $path) {
            $scanned[$path] = true;
        }
        $options['scanned_directories'] = array_keys($scanned);

        $options['cache_vary']['scanned_directories'] = $options['scanned_directories'];

        $fileFinder = new Finder();
        $fileFinder->files()->in($modulePaths)->name('*.*.*');

        $newResources = [];

        foreach ($fileFinder as $file) {
            if (preg_match('/\.([^.]+)\.[^.]+$/', $file->getFilename(), $matches)) {
                $locale = $matches[1];
                $newResources[$locale][$file->getRealPath()] = true;
            }
        }

        foreach ($newResources as $locale => $files) {
            $existing = array_flip($options['resource_files'][$locale] ?? []);
            $merged = array_replace($existing, $files);
            $options['resource_files'][$locale] = array_keys($merged);
        }

        $definition->setArgument(4, $options);
    }
}
