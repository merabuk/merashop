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
        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

        if (is_file($this->getConfigDir().'/services.yaml')) {
            $container->import($configDir.'/services.yaml');
            $container->import($configDir.'/{services}_'.$this->environment.'.yaml');
        } else {
            $container->import($configDir.'/{services}.php');
            $container->import($configDir.'/{services}_'.$this->environment.'.php');
        }

        $container->import($configDir.'/{modules}/*.{php,yaml}');
        $container->import($configDir.'/{modules}/'.$this->environment.'/*.{php,yaml}');
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterModuleTranslationsPass());
    }
}
