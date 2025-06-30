<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Exception\UsernameInvalidException;
use BizUserBundle\Repository\BizUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class BizUserRepositoryTest extends TestCase
{
    private ManagerRegistry $registry;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // 设置 getManager 方法返回模拟的 EntityManager
        $this->registry->method('getManager')->willReturn($this->entityManager);
        $this->registry->method('getManagerForClass')->willReturn($this->entityManager);
    }

    /**
     * 测试通过数字ID加载用户
     */
    public function testLoadUserByIdentifier_withNumericId(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setId(123);
        $user->setUsername('test_user');
        $user->setValid(true);

        // 创建一个部分模拟，只模拟 findOneBy 方法
        $repository = new class($this->registry) extends BizUserRepository {
            private ?BizUser $mockUser = null;
            private array $expectedCriteria = [];
            
            public function setMockUser(?BizUser $user): void {
                $this->mockUser = $user;
            }
            
            public function setExpectedCriteria(array $criteria): void {
                $this->expectedCriteria = $criteria;
            }
            
            public function findOneBy(array $criteria, ?array $orderBy = null): ?BizUser {
                if ($criteria === $this->expectedCriteria) {
                    return $this->mockUser;
                }
                return null;
            }
        };
        
        $repository->setExpectedCriteria(['id' => '123', 'valid' => true]);
        $repository->setMockUser($user);

        // 执行测试
        $result = $repository->loadUserByIdentifier('123');

        // 断言结果
        $this->assertSame($user, $result);
    }

    /**
     * 测试通过用户名加载用户
     */
    public function testLoadUserByIdentifier_withUsername(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('test_user');
        $user->setValid(true);

        // 创建一个部分模拟，只模拟 findOneBy 方法
        $repository = new class($this->registry) extends BizUserRepository {
            private ?BizUser $mockUser = null;
            
            public function setMockUser(?BizUser $user): void {
                $this->mockUser = $user;
            }
            
            public function findOneBy(array $criteria, ?array $orderBy = null): ?BizUser {
                // 直接检查用户名，因为 'test_user' 不是数字，不会作为 ID 查询
                if ($criteria === ['username' => 'test_user', 'valid' => true]) {
                    return $this->mockUser;
                }
                return null;
            }
        };
        
        $repository->setMockUser($user);

        // 执行测试
        $result = $repository->loadUserByIdentifier('test_user');

        // 断言结果
        $this->assertSame($user, $result);
    }

    /**
     * 测试加载无效用户返回 null
     */
    public function testLoadUserByIdentifier_withInvalidUser(): void
    {
        // 创建一个部分模拟，只模拟 findOneBy 方法
        $repository = new class($this->registry) extends BizUserRepository {
            public function findOneBy(array $criteria, ?array $orderBy = null): ?BizUser {
                // 无论什么条件都返回null，模拟用户不存在
                return null;
            }
        };

        // 执行测试
        $result = $repository->loadUserByIdentifier('non_existing');

        // 断言结果
        $this->assertNull($result);
    }

    /**
     * 测试返回保留用户名列表
     */
    public function testGetReservedUserNames(): void
    {
        // 创建一个新的实例，避免使用之前模拟的方法
        $repository = new BizUserRepository($this->registry);
        $result = $repository->getReservedUserNames();

        // 断言结果是数组且包含预期的保留用户名
        $this->assertContains('admin', $result);
        $this->assertContains('root', $result);
        $this->assertContains('system', $result);
        $this->assertGreaterThan(0, count($result));
    }

    /**
     * 测试合法用户名不抛出异常
     */
    public function testCheckUserLegal_withValidUsername(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('valid_user');

        // 创建一个新的实例，避免使用之前模拟的方法
        $repository = new BizUserRepository($this->registry);

        try {
            // 执行测试
            $repository->checkUserLegal($user);
            $this->assertTrue(true); // 如果没有异常，测试通过
        } catch (\Throwable $e) {
            $this->fail('方法抛出了意外的异常: ' . $e->getMessage());
        }
    }

    /**
     * 测试保留用户名抛出异常
     */
    public function testCheckUserLegal_withReservedUsername(): void
    {
        // 准备测试数据
        $user = new BizUser();
        $user->setUsername('admin');

        // 创建一个新的实例，避免使用之前模拟的方法
        $repository = new BizUserRepository($this->registry);

        // 断言方法抛出异常
        $this->expectException(UsernameInvalidException::class);
        $this->expectExceptionMessage('用户名不合法');

        // 执行测试
        $repository->checkUserLegal($user);
    }

    /**
     * 测试返回实体管理器
     */
    public function testEm_returnsEntityManager(): void
    {
        // 创建一个部分模拟，只模拟 getEntityManager 方法
        $repository = new class($this->registry) extends BizUserRepository {
            private EntityManagerInterface $em;
            
            public function setEntityManager(EntityManagerInterface $em): void {
                $this->em = $em;
            }
            
            protected function getEntityManager(): EntityManagerInterface {
                return $this->em;
            }
        };
        
        $repository->setEntityManager($this->entityManager);
        $result = $repository->em();

        // 断言结果是 EntityManagerInterface 的实例
        $this->assertInstanceOf(EntityManagerInterface::class, $result);
        $this->assertSame($this->entityManager, $result);
    }
}
