<?php

declare(strict_types=1);

namespace BizUserBundle\DataFixtures;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\PasswordHistory;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tourze\UserServiceContracts\UserServiceConstants;

/**
 * 密码历史记录数据填充
 *
 * 为系统用户创建密码修改历史记录，用于演示密码安全策略
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class PasswordHistoryFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 密码历史引用常量
    public const ADMIN_PASSWORD_HISTORY_REFERENCE = 'admin-password-history';
    public const MODERATOR_PASSWORD_HISTORY_REFERENCE = 'moderator-password-history';
    public const USER_PASSWORD_HISTORY_REFERENCE = 'user-password-history';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getGroups(): array
    {
        return [
            UserServiceConstants::USER_FIXTURES_NAME,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // 为管理员创建密码历史记录
        $adminUser = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);
        $this->createPasswordHistoryForUser($manager, $adminUser, [
            ['password' => 'admin123!', 'days_ago' => 90, 'need_reset' => false],
            ['password' => 'admin456@', 'days_ago' => 60, 'need_reset' => false],
            ['password' => 'admin789#', 'days_ago' => 30, 'need_reset' => false],
        ], self::ADMIN_PASSWORD_HISTORY_REFERENCE);

        // 为审核员创建密码历史记录
        $moderatorUser = $this->getReference(BizUserFixtures::MODERATOR_USER_REFERENCE, BizUser::class);
        $this->createPasswordHistoryForUser($manager, $moderatorUser, [
            ['password' => 'moderator123!', 'days_ago' => 75, 'need_reset' => false],
            ['password' => 'moderator456@', 'days_ago' => 45, 'need_reset' => false],
        ], self::MODERATOR_PASSWORD_HISTORY_REFERENCE);

        // 为普通用户创建密码历史记录
        for ($i = 1; $i <= 5; ++$i) {
            $user = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . $i, BizUser::class);

            $passwordHistories = [
                ['password' => "user{$i}pass123!", 'days_ago' => rand(30, 120), 'need_reset' => false],
            ];

            // 某些用户有多次密码修改记录（所有用户都有）
            $passwordHistories[] = ['password' => "user{$i}pass456@", 'days_ago' => rand(15, 29), 'need_reset' => false];

            // 某些用户需要重置密码
            if (3 === $i) {
                $passwordHistories[] = ['password' => "temp{$i}pass789#", 'days_ago' => rand(1, 14), 'need_reset' => true];
            }

            $reference = 1 === $i ? self::USER_PASSWORD_HISTORY_REFERENCE : null;
            $this->createPasswordHistoryForUser($manager, $user, $passwordHistories, $reference);
        }

        // 创建一些特殊的密码历史记录

        // 过期密码记录
        $expiredPasswordHistory = new PasswordHistory();
        $expiredPasswordHistory->setUsername('expired_user');
        $expiredPasswordHistory->setUserId('999999');
        $expiredPasswordHistory->setCiphertext($this->passwordHasher->hashPassword(new BizUser(), 'expired123!'));
        $expiredPasswordHistory->setExpireTime(CarbonImmutable::now()->modify('-30 days'));
        $expiredPasswordHistory->setCreateTime(CarbonImmutable::now()->modify('-90 days'));
        $expiredPasswordHistory->setCreatedFromIp('192.168.1.100');
        $manager->persist($expiredPasswordHistory);

        // 需要重置的密码记录
        $resetRequiredHistory = new PasswordHistory();
        $resetRequiredHistory->setNeedReset(true);
        $resetRequiredHistory->setUsername('reset_user');
        $resetRequiredHistory->setUserId('888888');
        $resetRequiredHistory->setCiphertext($this->passwordHasher->hashPassword(new BizUser(), 'reset456@'));
        $resetRequiredHistory->setExpireTime(CarbonImmutable::now()->modify('+7 days'));
        $resetRequiredHistory->setCreateTime(CarbonImmutable::now()->modify('-5 days'));
        $resetRequiredHistory->setCreatedFromIp('10.0.0.50');
        $manager->persist($resetRequiredHistory);

        // 来自不同IP的密码修改记录
        $ipVariationHistory = new PasswordHistory();
        $ipVariationHistory->setUsername('mobile_user');
        $ipVariationHistory->setUserId('777777');
        $ipVariationHistory->setCiphertext($this->passwordHasher->hashPassword(new BizUser(), 'mobile789#'));
        $ipVariationHistory->setCreateTime(CarbonImmutable::now()->modify('-2 days'));
        $ipVariationHistory->setCreatedFromIp('203.208.60.1'); // 移动网络IP
        $manager->persist($ipVariationHistory);

        $manager->flush();
    }

    /**
     * 为指定用户创建密码历史记录
     *
     * @param array<int, array<string, mixed>> $passwordData 密码数据数组，每个元素包含 password, days_ago, need_reset
     * @param string|null                      $reference    引用名称（可选）
     */
    private function createPasswordHistoryForUser(
        ObjectManager $manager,
        BizUser $user,
        array $passwordData,
        ?string $reference = null,
    ): void {
        $ips = [
            '192.168.1.10',
            '192.168.1.20',
            '10.0.0.100',
            '172.16.0.50',
            '203.208.60.100',
            '114.114.114.114',
        ];

        foreach ($passwordData as $index => $data) {
            $passwordHistory = new PasswordHistory();
            $passwordHistory->setNeedReset($data['need_reset'] ?? false);
            $passwordHistory->setUsername($user->getUsername());
            $passwordHistory->setUserId((string) $user->getId());
            $passwordHistory->setCiphertext($this->passwordHasher->hashPassword($user, $data['password']));

            $createTime = CarbonImmutable::now()->modify('-' . $data['days_ago'] . ' days');
            $passwordHistory->setCreateTime($createTime);

            // 设置过期时间（90天后过期）
            if (false === $data['need_reset']) {
                $passwordHistory->setExpireTime($createTime->modify('+90 days'));
            } else {
                // 需要重置的密码设置较短的有效期
                $passwordHistory->setExpireTime($createTime->modify('+30 days'));
            }

            // 随机分配IP地址
            $passwordHistory->setCreatedFromIp($ips[array_rand($ips)]);

            $manager->persist($passwordHistory);

            // 为第一个记录添加引用
            if (0 === $index && null !== $reference) {
                $this->addReference($reference, $passwordHistory);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            BizUserFixtures::class,
        ];
    }
}
