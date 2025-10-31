<?php

namespace BizUserBundle\Tests\DependencyInjection;

use BizUserBundle\DependencyInjection\BizUserExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(BizUserExtension::class)]
final class BizUserExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private BizUserExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new BizUserExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoadDoesNotThrowException(): void
    {
        // 我们只测试方法不会抛出异常
        $configs = [];

        $this->expectNotToPerformAssertions();
        $this->extension->load($configs, $this->container);
    }

    public function testExtensionAlias(): void
    {
        $this->assertEquals('biz_user', $this->extension->getAlias());
    }
}
