<?php

namespace BizUserBundle;

use BizUserBundle\Entity\BizUser;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\ResolveTargetEntityPass;

class BizUserBundle extends Bundle implements BundleDependencyInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new ResolveTargetEntityPass(UserInterface::class, BizUser::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }

    /**
     * @return array<class-string<Bundle>, array<string, bool>>
     */
    public static function getBundleDependencies(): array
    {
        return [
            \Tourze\DoctrineResolveTargetEntityBundle\DoctrineResolveTargetEntityBundle::class => ['all' => true],
            \Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle::class => ['all' => true],
            \Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class => ['all' => true],
        ];
    }
}
