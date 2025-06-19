<?php

namespace BizUserBundle\Service;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Entity\RoleEntityPermission;
use BizUserBundle\Entity\UserAttribute;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 业务用户系统菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('基础用户模块')) {
            $item->addChild('基础用户模块');
        }

        $userMenu = $item->getChild('基础用户模块');
        
        // 用户管理菜单
        $userMenu->addChild('用户管理')
            ->setUri($this->linkGenerator->getCurdListPage(BizUser::class))
            ->setAttribute('icon', 'fas fa-users');
        
        // 角色管理菜单
        $userMenu->addChild('角色管理')
            ->setUri($this->linkGenerator->getCurdListPage(BizRole::class))
            ->setAttribute('icon', 'fas fa-user-tag');
        
        // 用户属性管理菜单
        $userMenu->addChild('用户属性管理')
            ->setUri($this->linkGenerator->getCurdListPage(UserAttribute::class))
            ->setAttribute('icon', 'fas fa-user-cog');
        
        // 数据权限管理菜单
        $userMenu->addChild('数据权限管理')
            ->setUri($this->linkGenerator->getCurdListPage(RoleEntityPermission::class))
            ->setAttribute('icon', 'fas fa-shield-alt');
        
        // 密码历史记录菜单
        $userMenu->addChild('密码历史记录')
            ->setUri($this->linkGenerator->getCurdListPage(PasswordHistory::class))
            ->setAttribute('icon', 'fas fa-key');
    }
}
