<?php

namespace BizUserBundle\Tests\Unit;

use BizUserBundle\BizUserBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;

class BizUserBundleTest extends TestCase
{
    private BizUserBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new BizUserBundle();
    }

    public function testBuild(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        
        $container->expects($this->once())
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf(ResolveTargetEntityPass::class),
                PassConfig::TYPE_BEFORE_OPTIMIZATION,
                1000
            );

        $this->bundle->build($container);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = BizUserBundle::getBundleDependencies();

        $this->assertArrayHasKey(\Tourze\DoctrineResolveTargetEntityBundle\DoctrineResolveTargetEntityBundle::class, $dependencies);
        $this->assertArrayHasKey(\Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle::class, $dependencies);
        $this->assertArrayHasKey(\Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class, $dependencies);

        foreach ($dependencies as $bundle => $config) {
            $this->assertIsArray($config);
            $this->assertArrayHasKey('all', $config);
            $this->assertTrue($config['all']);
        }
    }
}