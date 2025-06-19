<?php

declare(strict_types=1);

namespace BizUserBundle\DataFixtures;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\RoleEntityPermission;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\UserServiceContracts\UserServiceConstants;

/**
 * 角色数据权限填充
 *
 * 为不同角色创建数据权限控制规则，用于演示行级数据权限功能
 */
class RoleEntityPermissionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 数据权限引用常量
    public const MODERATOR_USER_PERMISSION_REFERENCE = 'moderator-user-permission';
    public const CONTENT_MANAGER_CONTENT_PERMISSION_REFERENCE = 'content-manager-content-permission';
    public const REPORT_VIEWER_ORDER_PERMISSION_REFERENCE = 'report-viewer-order-permission';
    public const ANALYST_REPORT_PERMISSION_REFERENCE = 'analyst-report-permission';
    public const USER_PROFILE_PERMISSION_REFERENCE = 'user-profile-permission';

    public static function getGroups(): array
    {
        return [
            UserServiceConstants::USER_FIXTURES_NAME,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // 内容审核员：只能查看和管理有效用户
        $moderatorRole = $this->getReference(BizRoleFixtures::MODERATOR_ROLE_REFERENCE, BizRole::class);

        $moderatorUserPermission = new RoleEntityPermission();
        $moderatorUserPermission->setRole($moderatorRole);
        $moderatorUserPermission->setEntityClass('BizUserBundle\\Entity\\BizUser');
        $moderatorUserPermission->setStatement('valid = 1 AND type != \'admin\'');
        $moderatorUserPermission->setRemark('内容审核员只能管理有效的非管理员用户');
        $moderatorUserPermission->setValid(true);
        $moderatorUserPermission->setCreateTime(CarbonImmutable::now()->modify('-25 days'));
        $moderatorUserPermission->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));
        $manager->persist($moderatorUserPermission);
        $this->addReference(self::MODERATOR_USER_PERMISSION_REFERENCE, $moderatorUserPermission);

        // 内容管理员：可以管理特定类型的内容
        $contentManagerRole = $this->getReference(BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE, BizRole::class);

        $contentManagerContentPermission = new RoleEntityPermission();
        $contentManagerContentPermission->setRole($contentManagerRole);
        $contentManagerContentPermission->setEntityClass('App\\Entity\\Content');
        $contentManagerContentPermission->setStatement('status IN (\'draft\', \'pending\', \'published\') AND author_id IS NOT NULL');
        $contentManagerContentPermission->setRemark('内容管理员可以管理草稿、待审核和已发布的内容');
        $contentManagerContentPermission->setValid(true);
        $contentManagerContentPermission->setCreateTime(CarbonImmutable::now()->modify('-24 days'));
        $contentManagerContentPermission->setUpdateTime(CarbonImmutable::now()->modify('-8 days'));
        $manager->persist($contentManagerContentPermission);
        $this->addReference(self::CONTENT_MANAGER_CONTENT_PERMISSION_REFERENCE, $contentManagerContentPermission);

        // 报告查看者：只能查看特定时间范围的订单数据
        $reportViewerRole = $this->getReference(BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE, BizRole::class);

        $reportViewerOrderPermission = new RoleEntityPermission();
        $reportViewerOrderPermission->setRole($reportViewerRole);
        $reportViewerOrderPermission->setEntityClass('App\\Entity\\Order');
        $reportViewerOrderPermission->setStatement('create_time >= DATE_SUB(NOW(), INTERVAL 90 DAY) AND status != \'deleted\'');
        $reportViewerOrderPermission->setRemark('报告查看者只能查看近90天的非删除订单');
        $reportViewerOrderPermission->setValid(true);
        $reportViewerOrderPermission->setCreateTime(CarbonImmutable::now()->modify('-20 days'));
        $reportViewerOrderPermission->setUpdateTime(CarbonImmutable::now()->modify('-6 days'));
        $manager->persist($reportViewerOrderPermission);
        $this->addReference(self::REPORT_VIEWER_ORDER_PERMISSION_REFERENCE, $reportViewerOrderPermission);

        // 数据分析师：可以访问聚合报告数据
        $analystRole = $this->getReference(BizRoleFixtures::ANALYST_ROLE_REFERENCE, BizRole::class);

        $analystReportPermission = new RoleEntityPermission();
        $analystReportPermission->setRole($analystRole);
        $analystReportPermission->setEntityClass('App\\Entity\\AnalyticsReport');
        $analystReportPermission->setStatement('type IN (\'sales\', \'user_behavior\', \'performance\') AND is_public = 1');
        $analystReportPermission->setRemark('数据分析师可以访问公开的销售、用户行为和性能分析报告');
        $analystReportPermission->setValid(true);
        $analystReportPermission->setCreateTime(CarbonImmutable::now()->modify('-18 days'));
        $analystReportPermission->setUpdateTime(CarbonImmutable::now()->modify('-4 days'));
        $manager->persist($analystReportPermission);
        $this->addReference(self::ANALYST_REPORT_PERMISSION_REFERENCE, $analystReportPermission);

        // 普通用户：只能查看自己的个人资料
        $userRole = $this->getReference(BizRoleFixtures::USER_ROLE_REFERENCE, BizRole::class);

        $userProfilePermission = new RoleEntityPermission();
        $userProfilePermission->setRole($userRole);
        $userProfilePermission->setEntityClass('BizUserBundle\\Entity\\BizUser');
        $userProfilePermission->setStatement('id = :current_user_id');
        $userProfilePermission->setRemark('普通用户只能访问自己的个人资料');
        $userProfilePermission->setValid(true);
        $userProfilePermission->setCreateTime(CarbonImmutable::now()->modify('-15 days'));
        $userProfilePermission->setUpdateTime(CarbonImmutable::now()->modify('-2 days'));
        $manager->persist($userProfilePermission);
        $this->addReference(self::USER_PROFILE_PERMISSION_REFERENCE, $userProfilePermission);

        // 普通用户：只能查看自己的用户属性
        $userAttributePermission = new RoleEntityPermission();
        $userAttributePermission->setRole($userRole);
        $userAttributePermission->setEntityClass('BizUserBundle\\Entity\\UserAttribute');
        $userAttributePermission->setStatement('user_id = :current_user_id');
        $userAttributePermission->setRemark('普通用户只能访问自己的属性信息');
        $userAttributePermission->setValid(true);
        $userAttributePermission->setCreateTime(CarbonImmutable::now()->modify('-14 days'));
        $userAttributePermission->setUpdateTime(CarbonImmutable::now()->modify('-1 days'));
        $manager->persist($userAttributePermission);

        // 内容审核员：可以查看用户属性但不能修改
        $moderatorAttributePermission = new RoleEntityPermission();
        $moderatorAttributePermission->setRole($moderatorRole);
        $moderatorAttributePermission->setEntityClass('BizUserBundle\\Entity\\UserAttribute');
        $moderatorAttributePermission->setStatement('name NOT IN (\'api_token\', \'private_key\') AND user_id IN (SELECT id FROM biz_user WHERE valid = 1)');
        $moderatorAttributePermission->setRemark('内容审核员可以查看非敏感的用户属性');
        $moderatorAttributePermission->setValid(true);
        $moderatorAttributePermission->setCreateTime(CarbonImmutable::now()->modify('-12 days'));
        $moderatorAttributePermission->setUpdateTime(CarbonImmutable::now()->modify('-1 days'));
        $manager->persist($moderatorAttributePermission);

        // 数据分析师：可以查看密码历史统计但不能看到具体密码
        $analystPasswordPermission = new RoleEntityPermission();
        $analystPasswordPermission->setRole($analystRole);
        $analystPasswordPermission->setEntityClass('BizUserBundle\\Entity\\PasswordHistory');
        $analystPasswordPermission->setStatement('create_time >= DATE_SUB(NOW(), INTERVAL 180 DAY)');
        $analystPasswordPermission->setRemark('数据分析师可以查看近6个月的密码修改历史统计');
        $analystPasswordPermission->setValid(true);
        $analystPasswordPermission->setCreateTime(CarbonImmutable::now()->modify('-10 days'));
        $analystPasswordPermission->setUpdateTime(CarbonImmutable::now()->modify('-1 days'));
        $manager->persist($analystPasswordPermission);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BizRoleFixtures::class,
        ];
    }
}
