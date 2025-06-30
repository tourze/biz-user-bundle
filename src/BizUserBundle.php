<?php

namespace BizUserBundle;

use BizUserBundle\Entity\BizUser;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\DoctrineResolveTargetEntityBundle\DoctrineResolveTargetEntityBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineTrackBundle\DoctrineTrackBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;

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
            DoctrineResolveTargetEntityBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineSnowflakeBundle::class => ['all' => true],
            DoctrineIpBundle::class => ['all' => true],
            DoctrineTrackBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
        ];
    }
}
