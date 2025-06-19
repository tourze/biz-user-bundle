<?php

declare(strict_types=1);

namespace BizUserBundle\DataFixtures;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\UserAttribute;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\UserServiceContracts\UserServiceConstants;

/**
 * 用户属性数据填充
 *
 * 为系统用户创建各种属性配置，用于演示用户扩展属性功能
 */
class UserAttributeFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 用户属性引用常量
    public const ADMIN_AVATAR_ATTRIBUTE_REFERENCE = 'admin-avatar-attribute';
    public const ADMIN_PROFILE_ATTRIBUTE_REFERENCE = 'admin-profile-attribute';
    public const USER_PREFERENCE_ATTRIBUTE_REFERENCE = 'user-preference-attribute';
    public const USER_SETTING_ATTRIBUTE_REFERENCE = 'user-setting-attribute';

    // 属性名称常量
    private const ATTR_AVATAR_URL = 'avatar_url';
    private const ATTR_PROFILE_SUMMARY = 'profile_summary';
    private const ATTR_THEME_PREFERENCE = 'theme_preference';
    private const ATTR_NOTIFICATION_SETTING = 'notification_setting';
    private const ATTR_LAST_LOGIN_DEVICE = 'last_login_device';
    private const ATTR_API_TOKEN = 'api_token';

    public static function getGroups(): array
    {
        return [
            UserServiceConstants::USER_FIXTURES_NAME,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // 为管理员用户添加属性
        $adminUser = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

        // 管理员头像属性
        $adminAvatarAttr = new UserAttribute();
        $adminAvatarAttr->setUser($adminUser);
        $adminAvatarAttr->setName(self::ATTR_AVATAR_URL);
        $adminAvatarAttr->setValue('https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150');
        $adminAvatarAttr->setRemark('管理员头像URL');
        $adminAvatarAttr->setCreateTime(CarbonImmutable::now()->modify('-29 days'));
        $adminAvatarAttr->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));
        $manager->persist($adminAvatarAttr);
        $this->addReference(self::ADMIN_AVATAR_ATTRIBUTE_REFERENCE, $adminAvatarAttr);

        // 管理员个人简介属性
        $adminProfileAttr = new UserAttribute();
        $adminProfileAttr->setUser($adminUser);
        $adminProfileAttr->setName(self::ATTR_PROFILE_SUMMARY);
        $adminProfileAttr->setValue('系统架构师，负责整体技术架构设计和系统管理工作。拥有10年以上软件开发经验。');
        $adminProfileAttr->setRemark('管理员个人简介');
        $adminProfileAttr->setCreateTime(CarbonImmutable::now()->modify('-28 days'));
        $adminProfileAttr->setUpdateTime(CarbonImmutable::now()->modify('-8 days'));
        $manager->persist($adminProfileAttr);
        $this->addReference(self::ADMIN_PROFILE_ATTRIBUTE_REFERENCE, $adminProfileAttr);

        // 为审核员用户添加属性
        $moderatorUser = $this->getReference(BizUserFixtures::MODERATOR_USER_REFERENCE, BizUser::class);

        // 审核员主题偏好
        $moderatorThemeAttr = new UserAttribute();
        $moderatorThemeAttr->setUser($moderatorUser);
        $moderatorThemeAttr->setName(self::ATTR_THEME_PREFERENCE);
        $moderatorThemeAttr->setValue('{"theme": "dark", "language": "zh_CN", "sidebar_collapsed": false}');
        $moderatorThemeAttr->setRemark('界面主题偏好设置');
        $moderatorThemeAttr->setCreateTime(CarbonImmutable::now()->modify('-24 days'));
        $moderatorThemeAttr->setUpdateTime(CarbonImmutable::now()->modify('-5 days'));
        $manager->persist($moderatorThemeAttr);

        // 审核员通知设置
        $moderatorNotificationAttr = new UserAttribute();
        $moderatorNotificationAttr->setUser($moderatorUser);
        $moderatorNotificationAttr->setName(self::ATTR_NOTIFICATION_SETTING);
        $moderatorNotificationAttr->setValue('{"email": true, "sms": false, "push": true, "audit_alerts": true}');
        $moderatorNotificationAttr->setRemark('通知接收设置');
        $moderatorNotificationAttr->setCreateTime(CarbonImmutable::now()->modify('-23 days'));
        $moderatorNotificationAttr->setUpdateTime(CarbonImmutable::now()->modify('-3 days'));
        $manager->persist($moderatorNotificationAttr);

        // 为普通用户添加属性
        for ($i = 1; $i <= 5; $i++) {
            $user = $this->getReference(BizUserFixtures::NORMAL_USER_REFERENCE_PREFIX . $i, BizUser::class);

            // 用户偏好设置
            $userPreferenceAttr = new UserAttribute();
            $userPreferenceAttr->setUser($user);
            $userPreferenceAttr->setName(self::ATTR_THEME_PREFERENCE);
            $userPreferenceAttr->setValue('{"theme": "' . ($i % 2 === 0 ? 'light' : 'dark') . '", "language": "zh_CN"}');
            $userPreferenceAttr->setRemark('用户界面偏好');
            $userPreferenceAttr->setCreateTime(CarbonImmutable::now()->modify('-' . rand(15, 20) . ' days'));
            $userPreferenceAttr->setUpdateTime(CarbonImmutable::now()->modify('-' . rand(1, 7) . ' days'));
            $manager->persist($userPreferenceAttr);

            if ($i === 1) {
                $this->addReference(self::USER_PREFERENCE_ATTRIBUTE_REFERENCE, $userPreferenceAttr);
            }

            // 最后登录设备信息
            $devices = ['iPhone 14', 'Chrome/Windows', 'Safari/macOS', 'Android App', 'Firefox/Linux'];
            $userDeviceAttr = new UserAttribute();
            $userDeviceAttr->setUser($user);
            $userDeviceAttr->setName(self::ATTR_LAST_LOGIN_DEVICE);
            $userDeviceAttr->setValue($devices[$i - 1]);
            $userDeviceAttr->setRemark('最后登录设备信息');
            $userDeviceAttr->setCreateTime(CarbonImmutable::now()->modify('-' . rand(10, 15) . ' days'));
            $userDeviceAttr->setUpdateTime(CarbonImmutable::now()->modify('-' . rand(1, 5) . ' days'));
            $manager->persist($userDeviceAttr);

            if ($i === 1) {
                $this->addReference(self::USER_SETTING_ATTRIBUTE_REFERENCE, $userDeviceAttr);
            }
        }

        // 为特定用户添加 API Token 属性
        $contentManagerUser = $this->getReference("user-10", BizUser::class);
        $apiTokenAttr = new UserAttribute();
        $apiTokenAttr->setUser($contentManagerUser);
        $apiTokenAttr->setName(self::ATTR_API_TOKEN);
        $apiTokenAttr->setValue('sk-' . bin2hex(random_bytes(16)));
        $apiTokenAttr->setRemark('API访问令牌');
        $apiTokenAttr->setCreateTime(CarbonImmutable::now()->modify('-12 days'));
        $apiTokenAttr->setUpdateTime(CarbonImmutable::now()->modify('-2 days'));
        $manager->persist($apiTokenAttr);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BizUserFixtures::class,
        ];
    }
}
