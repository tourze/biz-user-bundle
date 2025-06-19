<?php

declare(strict_types=1);

namespace BizUserBundle\DataFixtures;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\BizUser;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * 用户数据填充
 */
class BizUserFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 常量定义引用名称
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const MODERATOR_USER_REFERENCE = 'moderator-user';
    public const NORMAL_USER_REFERENCE_PREFIX = 'normal-user-';

    // 密码默认值
    private const DEFAULT_PASSWORD = '1234qwqw';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 创建管理员用户
        $adminUser = new BizUser();
        $adminUser->setUsername('admin');
        $adminUser->setEmail('admin@example.com');
        $adminUser->addAssignRole($this->getReference(BizRoleFixtures::ADMIN_ROLE_REFERENCE, BizRole::class));
        $adminUser->setNickName('系统管理员');
        $adminUser->setMobile('13800000001');
        $adminUser->setPasswordHash($this->passwordHasher->hashPassword($adminUser, self::DEFAULT_PASSWORD));
        $adminUser->setValid(true);
        $adminUser->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $adminUser->setUpdateTime(CarbonImmutable::now()->modify('-30 days'));

        $manager->persist($adminUser);
        $this->addReference(self::ADMIN_USER_REFERENCE, $adminUser);

        // 创建内容审核员用户
        $moderatorUser = new BizUser();
        $moderatorUser->setUsername('moderator');
        $moderatorUser->setEmail('moderator@example.com');
        $moderatorUser->addAssignRole($this->getReference(BizRoleFixtures::MODERATOR_ROLE_REFERENCE, BizRole::class));
        $moderatorUser->setNickName('内容审核员');
        $moderatorUser->setMobile('13800000002');
        $moderatorUser->setPasswordHash($this->passwordHasher->hashPassword($moderatorUser, self::DEFAULT_PASSWORD));
        $moderatorUser->setValid(true);
        $moderatorUser->setCreateTime(CarbonImmutable::now()->modify('-25 days'));
        $moderatorUser->setUpdateTime(CarbonImmutable::now()->modify('-25 days'));

        $manager->persist($moderatorUser);
        $this->addReference(self::MODERATOR_USER_REFERENCE, $moderatorUser);

        // 创建一些特殊角色的用户
        $contentManagerUser = new BizUser();
        $contentManagerUser->setUsername('content_manager');
        $contentManagerUser->setEmail('content_manager@example.com');
        $contentManagerUser->addAssignRole($this->getReference(BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE, BizRole::class));
        $contentManagerUser->setNickName('内容管理员');
        $contentManagerUser->setMobile('13800000003');
        $contentManagerUser->setPasswordHash($this->passwordHasher->hashPassword($contentManagerUser, self::DEFAULT_PASSWORD));
        $contentManagerUser->setValid(true);
        $contentManagerUser->setCreateTime(CarbonImmutable::now()->modify('-24 days'));
        $contentManagerUser->setUpdateTime(CarbonImmutable::now()->modify('-24 days'));
        $manager->persist($contentManagerUser);

        $reportViewerUser = new BizUser();
        $reportViewerUser->setUsername('report_viewer');
        $reportViewerUser->setEmail('report_viewer@example.com');
        $reportViewerUser->addAssignRole($this->getReference(BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE, BizRole::class));
        $reportViewerUser->setNickName('报告查看者');
        $reportViewerUser->setMobile('13800000004');
        $reportViewerUser->setPasswordHash($this->passwordHasher->hashPassword($reportViewerUser, self::DEFAULT_PASSWORD));
        $reportViewerUser->setValid(true);
        $reportViewerUser->setCreateTime(CarbonImmutable::now()->modify('-23 days'));
        $reportViewerUser->setUpdateTime(CarbonImmutable::now()->modify('-23 days'));
        $manager->persist($reportViewerUser);

        $analystUser = new BizUser();
        $analystUser->setUsername('analyst');
        $analystUser->setEmail('analyst@example.com');
        $analystUser->addAssignRole($this->getReference(BizRoleFixtures::ANALYST_ROLE_REFERENCE, BizRole::class));
        $analystUser->setNickName('数据分析师');
        $analystUser->setMobile('13800000005');
        $analystUser->setPasswordHash($this->passwordHasher->hashPassword($analystUser, self::DEFAULT_PASSWORD));
        $analystUser->setValid(true);
        $analystUser->setCreateTime(CarbonImmutable::now()->modify('-22 days'));
        $analystUser->setUpdateTime(CarbonImmutable::now()->modify('-22 days'));
        $manager->persist($analystUser);

        // 创建普通用户
        for ($i = 1; $i <= 20; $i++) {
            $randomDays = rand(1, 20);
            $createTime = CarbonImmutable::now()->modify('-' . $randomDays . ' days');

            $user = new BizUser();
            $user->setUsername('user' . $i);
            $user->setEmail('user' . $i . '@example.com');
            $user->addAssignRole($this->getReference(BizRoleFixtures::USER_ROLE_REFERENCE, BizRole::class));
            $user->setNickName($faker->name());
            $user->setMobile('138' . str_pad((string)$i, 8, '0', STR_PAD_LEFT));
            $user->setPasswordHash($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
            $user->setValid(true);
            $user->setCreateTime($createTime);
            $user->setUpdateTime($createTime);

            $manager->persist($user);
            $this->addReference(self::NORMAL_USER_REFERENCE_PREFIX . $i, $user);
            $this->addReference("user-{$i}", $user);
        }

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
