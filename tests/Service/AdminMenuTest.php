<?php

namespace BizUserBundle\Tests\Service;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Service\AdminMenu;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        // 在容器中替换 LinkGeneratorInterface 服务
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);

        // 通过服务容器获取 AdminMenu
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testServiceCreation(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $reflection = new \ReflectionClass($adminMenu);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeAddsUserMenu(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);

        $mainItem = $this->createMock(ItemInterface::class);
        $userModuleItem = $this->createMock(ItemInterface::class);
        $userMenuItem = $this->createMock(ItemInterface::class);
        $passwordMenuItem = $this->createMock(ItemInterface::class);

        // 模拟LinkGenerator行为
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnMap([
                [BizUser::class, '/admin/bizuser/list'],
                [PasswordHistory::class, '/admin/passwordhistory/list'],
            ])
        ;

        // 第一次调用getChild返回null，第二次返回已创建的菜单项
        $mainItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('用户模块')
            ->willReturnOnConsecutiveCalls(null, $userModuleItem)
        ;

        // 创建用户模块父菜单
        $mainItem->expects($this->once())
            ->method('addChild')
            ->with('用户模块')
            ->willReturn($userModuleItem)
        ;

        // 添加两个子菜单
        $userModuleItem->expects($this->exactly(2))
            ->method('addChild')
            ->with(self::logicalOr('用户管理', '密码历史记录'))
            ->willReturnOnConsecutiveCalls($userMenuItem, $passwordMenuItem)
        ;

        // 设置用户管理菜单的URI和图标
        $userMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/bizuser/list')
            ->willReturn($userMenuItem)
        ;

        $userMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-users')
            ->willReturn($userMenuItem)
        ;

        // 设置密码历史记录菜单的URI和图标
        $passwordMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/passwordhistory/list')
            ->willReturn($passwordMenuItem)
        ;

        $passwordMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-key')
            ->willReturn($passwordMenuItem)
        ;

        $this->adminMenu->__invoke($mainItem);
    }

    public function testInvokeWithExistingUserMenu(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);

        $mainItem = $this->createMock(ItemInterface::class);
        $userModuleItem = $this->createMock(ItemInterface::class);
        $userMenuItem = $this->createMock(ItemInterface::class);
        $passwordMenuItem = $this->createMock(ItemInterface::class);

        // 模拟LinkGenerator行为
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnMap([
                [BizUser::class, '/admin/bizuser/list'],
                [PasswordHistory::class, '/admin/passwordhistory/list'],
            ])
        ;

        // 用户模块菜单已存在
        $mainItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('用户模块')
            ->willReturn($userModuleItem)
        ;

        // 不应该再次创建父菜单
        $mainItem->expects($this->never())
            ->method('addChild')
        ;

        // 添加两个子菜单
        $userModuleItem->expects($this->exactly(2))
            ->method('addChild')
            ->with(self::logicalOr('用户管理', '密码历史记录'))
            ->willReturnOnConsecutiveCalls($userMenuItem, $passwordMenuItem)
        ;

        // 设置URI和图标
        $userMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/bizuser/list')
            ->willReturn($userMenuItem)
        ;

        $userMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-users')
            ->willReturn($userMenuItem)
        ;

        $passwordMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/passwordhistory/list')
            ->willReturn($passwordMenuItem)
        ;

        $passwordMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-key')
            ->willReturn($passwordMenuItem)
        ;

        $this->adminMenu->__invoke($mainItem);
    }
}
