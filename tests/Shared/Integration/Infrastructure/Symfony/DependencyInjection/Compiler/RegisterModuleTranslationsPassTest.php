<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterModuleTranslationsPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class RegisterModuleTranslationsPassTest extends KernelTestCase
{
    public function testItRegistersModuleTranslations(): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.project_dir', __DIR__.str_repeat('/..', 5));

        $translatorDefinition = new Definition(null, [
            null, // container interface
            null, // formatter
            LocaleEnum::En->value,
            [], // loader ids
            [
                'resource_files' => [],
                'scanned_directories' => [],
            ], // options
        ]);
        $container->setDefinition('translator.default', $translatorDefinition);

        $pass = new RegisterModuleTranslationsPass();
        $pass->process($container);

        $options = $container->getDefinition('translator.default')->getArgument(4);

        self::assertNotEmpty($options['scanned_directories'], 'Translations directories should be scanned');

        $found = false;
        foreach ($options['scanned_directories'] as $path) {
            if (str_contains($path, 'src/Shared')) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found, 'Shared module translations directory should be registered');
    }
}
