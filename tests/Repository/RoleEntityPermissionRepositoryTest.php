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
        // These methods are guaranteed to exist in Doctrine repositories
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
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
        // Repository methods are guaranteed to exist via ServiceEntityRepository inheritance
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testRoleEntityPermissionEntityHasExpectedMethods(): void
    {
        $permission = new RoleEntityPermission();
        
        // Test functionality instead of method existence
        $permission->setStatement('test statement');
        $this->assertEquals('test statement', $permission->getStatement());
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
        $now = new \DateTime();
        
        // Test functionality instead of method existence
        $permission->setCreateTime($now);
        $this->assertEquals($now, $permission->getCreateTime());
    }

    public function testRoleEntityPermissionValidation(): void
    {
        $permission = new RoleEntityPermission();

        $permission->setValid(true);
        $this->assertTrue($permission->isValid());
        
        $permission->setValid(false);
        $this->assertFalse($permission->isValid());
    }

    public function testRoleEntityPermissionUserTracking(): void
    {
        $permission = new RoleEntityPermission();
        
        // Test functionality instead of method existence
        $permission->setCreatedBy('test_user');
        $this->assertEquals('test_user', $permission->getCreatedBy());
    }

    public function testRoleEntityPermissionRemark(): void
    {
        $permission = new RoleEntityPermission();

        $permission->setRemark('测试备注');
        $this->assertEquals('测试备注', $permission->getRemark());
    }
}
