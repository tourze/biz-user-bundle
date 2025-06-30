<?php

namespace BizUserBundle\Tests\Service;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Event\FindUserByIdentityEvent;
use BizUserBundle\Event\FindUsersByIdentityEvent;
use BizUserBundle\Exception\PasswordWeakStrengthException;
use BizUserBundle\Repository\BizUserRepository;
use BizUserBundle\Repository\PasswordHistoryRepository;
use BizUserBundle\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class UserServiceTest extends TestCase
{
    /**
     * 创建带有模拟依赖的UserService实例
     */
    private function createUserServiceWithMocks(
        ?BizUserRepository $userRepository = null,
        ?EventDispatcherInterface $eventDispatcher = null,
        ?PropertyAccessor $propertyAccessor = null,
        ?PasswordHasherFactoryInterface $hasherFactory = null,
        ?PasswordHistoryRepository $historyRepository = null,
        ?LoggerInterface $logger = null
    ): UserService {
        return new UserService(
            $userRepository ?? $this->createMock(BizUserRepository::class),
            $eventDispatcher ?? $this->createMock(EventDispatcherInterface::class),
            $propertyAccessor ?? $this->createMock(PropertyAccessor::class),
            $hasherFactory ?? $this->createMock(PasswordHasherFactoryInterface::class),
            $historyRepository ?? $this->createMock(PasswordHistoryRepository::class),
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }

    /**
     * 通过用户名查找用户
     */
    public function testFindUserByIdentity_withExistingUsername(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('test_user');
        $user->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(1))
            ->method('findOneBy')
            ->with(['valid' => true, 'username' => 'test_user'])
            ->willReturn($user);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository);

        // 执行方法
        $result = $service->findUserByIdentity('test_user');

        // 断言结果
        $this->assertSame($user, $result);
    }

    /**
     * 通过身份标识查找用户
     */
    public function testFindUserByIdentity_withExistingIdentity(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setIdentity('user_identity');
        $user->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($user) {
                if (isset($criteria['username']) && $criteria['username'] === 'user_identity') {
                    return null; // 第一次调用返回 null
                }
                if (isset($criteria['identity']) && $criteria['identity'] === 'user_identity') {
                    return $user; // 第二次调用返回 user
                }
                return null;
            });

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository);

        // 执行方法
        $result = $service->findUserByIdentity('user_identity');

        // 断言结果
        $this->assertSame($user, $result);
    }

    /**
     * 通过事件监听器查找用户
     */
    public function testFindUserByIdentity_withEventListener(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('event_user');
        $user->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        // 模拟 eventDispatcher
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($user) {
                $this->assertInstanceOf(FindUserByIdentityEvent::class, $event);
                $this->assertEquals('event_user', $event->getIdentity());
                // 设置事件的响应
                $event->setUser($user);
                return true;
            }))
            ->willReturnArgument(0);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository, $eventDispatcher);

        // 执行方法
        $result = $service->findUserByIdentity('event_user');

        // 断言结果
        $this->assertSame($user, $result);
    }

    /**
     * 当标识不存在时返回null
     */
    public function testFindUserByIdentity_withNonExistingIdentity(): void
    {
        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        // 模拟 eventDispatcher
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                $this->assertInstanceOf(FindUserByIdentityEvent::class, $event);
                $this->assertEquals('non_existing', $event->getIdentity());
                return true;
            }))
            ->willReturnArgument(0);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository, $eventDispatcher);

        // 执行方法
        $result = $service->findUserByIdentity('non_existing');

        // 断言结果
        $this->assertNull($result);
    }

    /**
     * 通过用户名查找多个用户
     */
    public function testFindUsersByIdentity_withExistingUsername(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('test_user');
        $user->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($user) {
                if (isset($criteria['username']) && $criteria['username'] === 'test_user') {
                    return $user; // 第一次调用返回 user
                }
                if (isset($criteria['identity']) && $criteria['identity'] === 'test_user') {
                    return null; // 第二次调用返回 null
                }
                return null;
            });

        // 模拟 eventDispatcher
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($user) {
                $this->assertInstanceOf(FindUsersByIdentityEvent::class, $event);
                $this->assertEquals('test_user', $event->getIdentity());
                $collection = new ArrayCollection();
                $collection->add($user);
                $this->assertEquals($collection->toArray(), $event->getUsers()->toArray());
                return true;
            }))
            ->willReturnArgument(0);

        // 创建 service 实例，注入环境变量
        $service = $this->createUserServiceWithMocks($userRepository, $eventDispatcher);

        // 执行方法
        $result = $service->findUsersByIdentity('test_user');

        // 断言结果
        $this->assertCount(1, $result);
        $this->assertContains($user, $result);
    }

    /**
     * 通过昵称查找多个用户（当环境变量启用时）
     */
    public function testFindUsersByIdentity_withNickname(): void
    {
        // 设置环境变量
        $_ENV['FIND_USER_BY_NICKNAME'] = 'true';

        // 准备测试数据
        $user1 = new BizUser();
        $user1->setUsername('user1');
        $user1->setNickName('nickname');
        $user1->setValid(true);

        $user2 = new BizUser();
        $user2->setUsername('user2');
        $user2->setNickName('nickname');
        $user2->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $userRepository->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true, 'nickName' => 'nickname'])
            ->willReturn([$user1, $user2]);

        // 模拟 eventDispatcher
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnArgument(0);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository, $eventDispatcher);

        // 执行方法
        $result = $service->findUsersByIdentity('nickname');

        // 断言结果
        $this->assertCount(2, $result);

        // 重置环境变量
        unset($_ENV['FIND_USER_BY_NICKNAME']);
    }

    /**
     * 测试结果中的去重功能
     */
    public function testFindUsersByIdentity_withDuplicates(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('duplicate_user');
        $user->setValid(true);

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($user) {
                return $user; // 两次调用都返回相同的 user
            });

        // 模拟 eventDispatcher
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                $this->assertInstanceOf(FindUsersByIdentityEvent::class, $event);
                return true;
            }))
            ->willReturnArgument(0);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks($userRepository, $eventDispatcher);

        // 执行方法
        $result = $service->findUsersByIdentity('duplicate_user');

        // 断言结果 - 去重后应该只有一个
        $this->assertCount(1, $result);
        $this->assertContains($user, $result);
    }

    /**
     * 测试合并用户功能
     */
    public function testMigrate_withValidUsers(): void
    {
        // 准备测试数据
        $sourceUser = new BizUser();
        $sourceUser->setId(1);
        $sourceUser->setUsername('source_user');

        $targetUser = new BizUser();
        $targetUser->setId(2);
        $targetUser->setUsername('target_user');

        // 模拟 EntityManager 和 Metadata
        $metadataFactory = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadataFactory::class);
        $metadata = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadata::class);
        $metadata->method('getName')->willReturn('TestEntity'); // 不是BizUser
        $metadata->method('getReflectionClass')->willReturn(
            new \ReflectionClass(\stdClass::class) // 使用标准类作为测试
        );

        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->method('getMetadataFactory')->willReturn($metadataFactory);
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('commit');

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->method('em')->willReturn($entityManager);

        // 模拟 propertyAccessor
        $propertyAccessor = $this->createMock(PropertyAccessor::class);

        // 模拟 logger
        $logger = $this->createMock(LoggerInterface::class);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks(
            $userRepository,
            null,
            $propertyAccessor,
            null,
            null,
            $logger
        );

        // 执行方法
        $service->migrate($sourceUser, $targetUser);

        // 成功执行无异常即视为通过
        $this->assertTrue(true);
    }

    /**
     * 测试合并用户时的异常处理
     */
    public function testMigrate_withExceptionHandling(): void
    {
        // 准备测试数据
        $sourceUser = new BizUser();
        $sourceUser->setId(1);
        $sourceUser->setUsername('source_user');

        $targetUser = new BizUser();
        $targetUser->setId(2);
        $targetUser->setUsername('target_user');

        // 模拟抛出异常的 EntityManager
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->method('beginTransaction');
        $entityManager->method('getMetadataFactory')->willThrowException(new \Exception('Test exception'));
        $entityManager->expects($this->once())->method('rollback');

        // 模拟 userRepository
        $userRepository = $this->createMock(BizUserRepository::class);
        $userRepository->method('em')->willReturn($entityManager);

        // 模拟 logger
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('合并用户时发生异常', $this->anything());

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks(
            $userRepository,
            null,
            null,
            null,
            null,
            $logger
        );

        // 执行方法
        $service->migrate($sourceUser, $targetUser);

        // 确认方法正常完成而不是传播异常
        $this->assertTrue(true);
    }


    /**
     * 测试密码长度不足抛出异常
     */
    public function testCheckNewPasswordStrength_withShortPassword(): void
    {
        // 准备测试数据
        $user = new BizUser();

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks();

        // 断言方法抛出异常
        $this->expectException(PasswordWeakStrengthException::class);
        $this->expectExceptionMessage('密码长度至少 8 位');

        $service->checkNewPasswordStrength($user, 'short');
    }

    /**
     * 测试密码复杂度不足抛出异常
     */
    public function testCheckNewPasswordStrength_withWeakComplexity(): void
    {
        // 准备测试数据
        $user = new BizUser();

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks();

        // 断言方法抛出异常
        $this->expectException(PasswordWeakStrengthException::class);
        $this->expectExceptionMessage('密码至少包含大写字母、小写字母、阿拉伯数字、特殊字符中的 3 种');

        // 只有小写字母和数字，不满足复杂度要求
        $service->checkNewPasswordStrength($user, 'password12345');
    }

    /**
     * 测试管理员用户检查
     */
    public function testIsAdmin_withAdminUser(): void
    {
        // 准备测试数据
        $adminRole = $this->createMock(\BizUserBundle\Entity\BizRole::class);
        $adminRole->method('isValid')->willReturn(true);
        $adminRole->method('isAdmin')->willReturn(true);
        
        $user = $this->createMock(BizUser::class);
        $user->method('getAssignRoles')->willReturn([$adminRole]);

        // 创建真实的 UserService 实例
        $service = $this->createUserServiceWithMocks();

        // 执行方法并断言结果
        $this->assertTrue($service->isAdmin($user));
    }

    /**
     * 测试非管理员用户检查
     */
    public function testIsAdmin_withNonAdminUser(): void
    {
        // 准备测试数据
        $user = $this->createMock(BizUser::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        // 创建 service 实例
        $service = $this->createUserServiceWithMocks();

        // 执行方法并断言结果
        $this->assertFalse($service->isAdmin($user));
    }
}
