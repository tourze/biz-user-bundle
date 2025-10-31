<?php

namespace BizUserBundle\Tests\Service;

use BizUserBundle\BizUserBundle;
use BizUserBundle\Entity\BizUser;
use BizUserBundle\Exception\PasswordWeakStrengthException;
use BizUserBundle\Repository\BizUserRepository;
use BizUserBundle\Service\UserService;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BizRoleBundle\BizRoleBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(UserService::class)]
#[RunTestsInSeparateProcesses]
final class UserServiceTest extends AbstractIntegrationTestCase
{
    private UserService $userService;

    /**
     * @return array<class-string, array<string, bool>>
     */
    public static function configureBundles(): array
    {
        return [
            FrameworkBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            BizUserBundle::class => ['all' => true],
            BizRoleBundle::class => ['all' => true],
        ];
    }

    protected function onSetUp(): void
    {
        $this->userService = self::getService(UserService::class);
    }

    public function testServiceIsAccessible(): void
    {
        $this->assertInstanceOf(UserService::class, $this->userService);
    }

    public function testFindUserByIdentityWithNonExistentUser(): void
    {
        $result = $this->userService->findUserByIdentity('non_existent_user_12345');

        $this->assertNull($result);
    }

    public function testFindUsersByIdentityWithNonExistentUser(): void
    {
        $result = $this->userService->findUsersByIdentity('non_existent_user_12345');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testIsAdminWithNonAdminUser(): void
    {
        $user = new BizUser();
        // 没有分配任何角色的用户不是管理员

        $result = $this->userService->isAdmin($user);

        $this->assertFalse($result);
    }

    public function testCheckNewPasswordStrengthWithStrongPassword(): void
    {
        $user = new BizUser();
        $user->setUsername('testuser');

        $this->expectNotToPerformAssertions();
        $this->userService->checkNewPasswordStrength($user, 'StrongP@ssw0rd123');
    }

    public function testCheckNewPasswordStrengthWithWeakPassword(): void
    {
        $user = new BizUser();
        $user->setUsername('testuser');

        $this->expectException(PasswordWeakStrengthException::class);
        $this->userService->checkNewPasswordStrength($user, 'weak');
    }

    public function testCheckNewPasswordStrengthWithSameAsUsername(): void
    {
        $user = new BizUser();
        $user->setUsername('testuser');

        $this->expectException(PasswordWeakStrengthException::class);
        $this->userService->checkNewPasswordStrength($user, 'testuser');
    }

    public function testCreateUserViaRepository(): void
    {
        $repository = self::getService(BizUserRepository::class);
        $user = $repository->createUser('test_user', 'Test User', 'avatar.jpg');

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(BizUser::class, $user);
        $this->assertEquals('test_user', $user->getUsername());
        $this->assertEquals('Test User', $user->getNickName());
        $this->assertEquals('avatar.jpg', $user->getAvatar());
        $this->assertTrue($user->isValid());
    }

    public function testMigrate(): void
    {
        $sourceUser = new BizUser();
        $sourceUser->setUsername('source_user');
        $sourceUser->setNickName('Source User');

        $targetUser = new BizUser();
        $targetUser->setUsername('target_user');
        $targetUser->setNickName('Target User');

        $this->expectNotToPerformAssertions();
        $this->userService->migrate($sourceUser, $targetUser);
    }

    public function testSaveUserViaRepository(): void
    {
        $repository = self::getService(BizUserRepository::class);
        $user = new BizUser();
        $user->setUsername('test_save_user_' . uniqid());
        $user->setNickName('Test Save User');
        $user->setValid(true);

        $this->expectNotToPerformAssertions();
        $repository->saveUser($user);
    }

    public function testFindOrCreateUserByMobileWithNonExistentUser(): void
    {
        $mobile = '13800000000';
        $user = $this->userService->findOrCreateUserByMobile($mobile);

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(BizUser::class, $user);
        $this->assertEquals($mobile, $user->getUsername());
        $this->assertTrue($user->isValid());
    }
}
