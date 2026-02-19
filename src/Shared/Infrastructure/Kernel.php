<?php

namespace App\Shared\Infrastructure;

use App\Shared\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterModuleTranslationsPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder,
    ): void {
        $extension = is_file($this->getConfigDir().'/services.yaml') ? 'yaml' : 'php';

        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/services.'.$extension);
        $container->import($configDir.'/{modules}/*.{php,yaml}');

        $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');
        $container->import($configDir.'/{modules}/'.$this->environment.'/*.{php,yaml}');
        $container->import($configDir.'/{services}_'.$this->environment.'.'.$extension);
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterModuleTranslationsPass());
    }
}
