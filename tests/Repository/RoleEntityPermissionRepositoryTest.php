<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\RoleEntityPermission;
use BizUserBundle\Repository\RoleEntityPermissionRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * 测试 RoleEntityPermissionRepository 类
 */
class RoleEntityPermissionRepositoryTest extends TestCase
{
    private RoleEntityPermissionRepository $repository;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $registry->method('getManagerForClass')->willReturn($entityManager);

        $this->repository = new RoleEntityPermissionRepository($registry);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testInheritsFromServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
        $this->assertInstanceOf(EntityRepository::class, $this->repository);
    }

    public function testBasicRepositoryMethods(): void
    {
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'findOneBy'));
        $this->assertTrue(method_exists($this->repository, 'findAll'));
        $this->assertTrue(method_exists($this->repository, 'findBy'));
    }

    public function testRepositoryCanHandleRoleEntityPermissionEntity(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setStatement('id = :userId');

        $this->assertEquals('id = :userId', $permission->getStatement());
    }

    public function testRepositoryClassMethods(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $docComment = $reflection->getDocComment();

        $this->assertStringContainsString('@method RoleEntityPermission|null find(', $docComment);
        $this->assertStringContainsString('@method RoleEntityPermission|null findOneBy(', $docComment);
        $this->assertStringContainsString('@method RoleEntityPermission[]    findAll()', $docComment);
        $this->assertStringContainsString('@method RoleEntityPermission[]    findBy(', $docComment);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $parentClass = $reflection->getParentClass();

        $this->assertEquals(ServiceEntityRepository::class, $parentClass->getName());
    }

    public function testRepositoryMethodsWithEntityTypes(): void
    {
        $methods = ['find', 'findOneBy', 'findAll', 'findBy'];

        foreach ($methods as $methodName) {
            $this->assertTrue(
                method_exists($this->repository, $methodName),
                sprintf('方法 %s 应该存在', $methodName)
            );
        }
    }

    public function testRoleEntityPermissionEntityHasExpectedMethods(): void
    {
        $permission = new RoleEntityPermission();

        $this->assertTrue(method_exists($permission, 'setStatement'));
        $this->assertTrue(method_exists($permission, 'getStatement'));
        $this->assertTrue(method_exists($permission, 'setRole'));
        $this->assertTrue(method_exists($permission, 'getRole'));
        $this->assertTrue(method_exists($permission, 'setEntityClass'));
        $this->assertTrue(method_exists($permission, 'getEntityClass'));
    }

    public function testRoleEntityPermissionStringRepresentation(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setStatement('id = 1');

        $this->assertEquals('id = 1', $permission->getStatement());
    }

    public function testRoleEntityPermissionTimestamps(): void
    {
        $permission = new RoleEntityPermission();

        $this->assertTrue(method_exists($permission, 'setCreateTime'));
        $this->assertTrue(method_exists($permission, 'getCreateTime'));
        $this->assertTrue(method_exists($permission, 'setUpdateTime'));
        $this->assertTrue(method_exists($permission, 'getUpdateTime'));
    }

    public function testRoleEntityPermissionValidation(): void
    {
        $permission = new RoleEntityPermission();

        $this->assertTrue(method_exists($permission, 'setValid'));
        $this->assertTrue(method_exists($permission, 'isValid'));

        $permission->setValid(true);
        $this->assertTrue($permission->isValid());
    }

    public function testRoleEntityPermissionUserTracking(): void
    {
        $permission = new RoleEntityPermission();

        $this->assertTrue(method_exists($permission, 'setCreatedBy'));
        $this->assertTrue(method_exists($permission, 'getCreatedBy'));
        $this->assertTrue(method_exists($permission, 'setUpdatedBy'));
        $this->assertTrue(method_exists($permission, 'getUpdatedBy'));
    }

    public function testRoleEntityPermissionRemark(): void
    {
        $permission = new RoleEntityPermission();

        $this->assertTrue(method_exists($permission, 'setRemark'));
        $this->assertTrue(method_exists($permission, 'getRemark'));

        $permission->setRemark('测试备注');
        $this->assertEquals('测试备注', $permission->getRemark());
    }
}
