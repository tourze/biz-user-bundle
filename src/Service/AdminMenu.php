<?php

namespace BizUserBundle\Service;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\PasswordHistory;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 业务用户系统菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private ?LinkGeneratorInterface $linkGenerator = null,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $this->linkGenerator) {
            return;
        }

        if (null === $item->getChild('用户模块')) {
            $item->addChild('用户模块');
        }

        $userMenu = $item->getChild('用户模块');

        if (null === $userMenu) {
            return;
        }

        // 用户管理菜单
        $userMenu->addChild('用户管理')
            ->setUri($this->linkGenerator->getCurdListPage(BizUser::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        // 密码历史记录菜单
        $userMenu->addChild('密码历史记录')
            ->setUri($this->linkGenerator->getCurdListPage(PasswordHistory::class))
            ->setAttribute('icon', 'fas fa-key')
        ;
    }
}
