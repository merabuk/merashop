<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Infrastructure\Symfony\DependencyInjection;

use App\Shared\Infrastructure\Symfony\DependencyInjection\SharedExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

final class SharedExtensionTest extends KernelTestCase
{
    public function testItPrependsTranslatorPaths(): void
    {
        $container = new ContainerBuilder();
        $filesystem = new Filesystem();

        $tmpDir = sys_get_temp_dir().'/merashop_extension_test_'.uniqid();
        $filesystem->mkdir($tmpDir.'/src/ModuleA/Presentation/translations');

        $container->setParameter('kernel.project_dir', $tmpDir);

        $extension = new SharedExtension();

        $extension->prepend($container);

        $config = $container->getExtensionConfig('framework');

        self::assertCount(1, $config);
        self::assertArrayHasKey('translator', $config[0]);
        self::assertContains(
            realpath($tmpDir.'/src/ModuleA/Presentation/translations'),
            $config[0]['translator']['paths']
        );

        $filesystem->remove($tmpDir);
    }
}
