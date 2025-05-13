<?php

namespace BizUserBundle\DependencyInjection;

use BizUserBundle\Entity\BizUser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\ResolveTargetEntityPass;

class BizUserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $container->addCompilerPass(
            new ResolveTargetEntityPass(UserInterface::class, BizUser::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }
}
