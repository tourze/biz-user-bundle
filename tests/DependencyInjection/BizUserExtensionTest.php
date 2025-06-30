<?php

namespace BizUserBundle\Tests\DependencyInjection;

use BizUserBundle\DependencyInjection\BizUserExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BizUserExtensionTest extends TestCase
{
    private BizUserExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new BizUserExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务已加载
        self::assertTrue($this->container->has('biz-user.property-accessor'));
        self::assertTrue($this->container->hasDefinition('biz-user.property-accessor'));
    }
}