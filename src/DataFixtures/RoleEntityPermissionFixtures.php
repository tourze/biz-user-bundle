<?php

declare(strict_types=1);

namespace BizUserBundle\DataFixtures;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\RoleEntityPermission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * 角色实体权限数据填充
 *
 * 定义角色对实体的权限规则，如：查看、编辑、删除等
 */
class RoleEntityPermissionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 实体类型
    private const ENTITIES = [
        'App\\Entity\\GeneratedContent' => 'AI生成内容',
        'App\\Entity\\RiskKeyword' => '风险关键词',
        'App\\Entity\\Report' => '举报记录',
        'App\\Entity\\ViolationRecord' => '违规记录',
        'BizUserBundle\\Entity\\BizUser' => '用户',
        'BizUserBundle\\Entity\\BizRole' => '角色'
    ];

    // 操作权限
    private const ACTIONS = [
        'view' => '查看',
        'add' => '添加',
        'edit' => '编辑',
        'delete' => '删除',
        'export' => '导出',
        'import' => '导入'
    ];

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        // 管理员角色拥有所有实体的所有权限
        $adminRole = $this->getReference(BizRoleFixtures::ADMIN_ROLE_REFERENCE, BizRole::class);
        foreach (self::ENTITIES as $entityClass => $entityTitle) {
            foreach (self::ACTIONS as $action => $actionTitle) {
                $permission = new RoleEntityPermission();
                $permission->setRole($adminRole);
                $permission->setEntityClass($entityClass);
                $permission->setStatement('1=1'); // 允许所有
                $permission->setRemark(sprintf('%s对%s的%s权限', $adminRole->getTitle(), $entityTitle, $actionTitle));
                $permission->setValid(true);
                $permission->setCreateTime($now);
                $permission->setUpdateTime($now);
                $manager->persist($permission);
            }
        }

        // 审核员角色权限
        $moderatorRole = $this->getReference(BizRoleFixtures::MODERATOR_ROLE_REFERENCE, BizRole::class);
        $contentEntities = ['App\\Entity\\GeneratedContent', 'App\\Entity\\RiskKeyword', 'App\\Entity\\Report', 'App\\Entity\\ViolationRecord'];

        foreach ($contentEntities as $entityClass) {
            foreach (self::ACTIONS as $action => $actionTitle) {
                // 审核员不能删除记录
                if ($action === 'delete' && $entityClass !== 'App\\Entity\\RiskKeyword') {
                    continue;
                }

                $permission = new RoleEntityPermission();
                $permission->setRole($moderatorRole);
                $permission->setEntityClass($entityClass);
                $permission->setStatement('1=1'); // 允许所有
                $permission->setRemark(sprintf('%s对%s的%s权限', $moderatorRole->getTitle(), self::ENTITIES[$entityClass], $actionTitle));
                $permission->setValid(true);
                $permission->setCreateTime($now);
                $permission->setUpdateTime($now);
                $manager->persist($permission);
            }
        }

        // 内容管理员权限
        $contentManagerRole = $this->getReference(BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE, BizRole::class);
        $contentManagerEntities = ['App\\Entity\\GeneratedContent', 'App\\Entity\\RiskKeyword'];
        $contentManagerActions = ['view', 'add', 'edit', 'delete'];

        foreach ($contentManagerEntities as $entityClass) {
            foreach ($contentManagerActions as $action) {
                $permission = new RoleEntityPermission();
                $permission->setRole($contentManagerRole);
                $permission->setEntityClass($entityClass);
                $permission->setStatement('1=1'); // 允许所有
                $permission->setRemark(sprintf('%s对%s的%s权限', $contentManagerRole->getTitle(), self::ENTITIES[$entityClass], self::ACTIONS[$action]));
                $permission->setValid(true);
                $permission->setCreateTime($now);
                $permission->setUpdateTime($now);
                $manager->persist($permission);
            }
        }

        // 报表查看者权限
        $reportViewerRole = $this->getReference(BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE, BizRole::class);
        $reportViewerEntities = ['App\\Entity\\GeneratedContent', 'App\\Entity\\Report', 'App\\Entity\\ViolationRecord'];

        foreach ($reportViewerEntities as $entityClass) {
            $permission = new RoleEntityPermission();
            $permission->setRole($reportViewerRole);
            $permission->setEntityClass($entityClass);
            $permission->setStatement('1=1'); // 允许所有
            $permission->setRemark(sprintf('%s对%s的%s权限', $reportViewerRole->getTitle(), self::ENTITIES[$entityClass], self::ACTIONS['view']));
            $permission->setValid(true);
            $permission->setCreateTime($now);
            $permission->setUpdateTime($now);
            $manager->persist($permission);

            // 添加导出权限
            $permission = new RoleEntityPermission();
            $permission->setRole($reportViewerRole);
            $permission->setEntityClass($entityClass);
            $permission->setStatement('1=1'); // 允许所有
            $permission->setRemark(sprintf('%s对%s的%s权限', $reportViewerRole->getTitle(), self::ENTITIES[$entityClass], self::ACTIONS['export']));
            $permission->setValid(true);
            $permission->setCreateTime($now);
            $permission->setUpdateTime($now);
            $manager->persist($permission);
        }

        // 分析师权限
        $analystRole = $this->getReference(BizRoleFixtures::ANALYST_ROLE_REFERENCE, BizRole::class);
        $analystEntities = ['App\\Entity\\GeneratedContent', 'App\\Entity\\RiskKeyword', 'App\\Entity\\Report', 'App\\Entity\\ViolationRecord'];
        $analystActions = ['view', 'export'];

        foreach ($analystEntities as $entityClass) {
            foreach ($analystActions as $action) {
                $permission = new RoleEntityPermission();
                $permission->setRole($analystRole);
                $permission->setEntityClass($entityClass);
                $permission->setStatement('1=1'); // 允许所有
                $permission->setRemark(sprintf('%s对%s的%s权限', $analystRole->getTitle(), self::ENTITIES[$entityClass], self::ACTIONS[$action]));
                $permission->setValid(true);
                $permission->setCreateTime($now);
                $permission->setUpdateTime($now);
                $manager->persist($permission);
            }
        }

        // 用户角色（基本权限）
        $userRole = $this->getReference(BizRoleFixtures::USER_ROLE_REFERENCE, BizRole::class);
        $viewPermission = new RoleEntityPermission();
        $viewPermission->setRole($userRole);
        $viewPermission->setEntityClass('App\\Entity\\GeneratedContent');
        $viewPermission->setStatement('user_id = {user_id}'); // 只允许查看自己的内容
        $viewPermission->setRemark(sprintf('%s对%s的%s权限（仅自己创建的）', $userRole->getTitle(), self::ENTITIES['App\\Entity\\GeneratedContent'], self::ACTIONS['view']));
        $viewPermission->setValid(true);
        $viewPermission->setCreateTime($now);
        $viewPermission->setUpdateTime($now);
        $manager->persist($viewPermission);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BizRoleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['user'];
    }
}
