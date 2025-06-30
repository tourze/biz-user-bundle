<?php

namespace BizUserBundle\Tests\Service;

use BizUserBundle\Service\AdminMenu;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface $linkGenerator;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testInvoke(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $userMenu = $this->createMock(ItemInterface::class);
        
        // 设置期望的行为 - getChild 会被调用两次
        $item->expects($this->exactly(2))
            ->method('getChild')
            ->with('用户模块')
            ->willReturnOnConsecutiveCalls(null, $userMenu);
            
        $item->expects($this->once())
            ->method('addChild')
            ->with('用户模块')
            ->willReturn($userMenu);
            
        // 设置 linkGenerator 的期望返回值
        $this->linkGenerator->expects($this->exactly(5))
            ->method('getCurdListPage')
            ->willReturn('/admin/list');
            
        // 设置 userMenu 的期望行为
        $userMenu->expects($this->exactly(5))
            ->method('addChild')
            ->willReturnSelf();
        
        $userMenu->expects($this->exactly(5))
            ->method('setUri')
            ->willReturnSelf();
            
        $userMenu->expects($this->exactly(5))
            ->method('setAttribute')
            ->willReturnSelf();
        
        // 执行测试
        ($this->adminMenu)($item);
    }
}