<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Repository\BizRoleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * 测试 BizRoleRepository 类
 */
class BizRoleRepositoryTest extends TestCase
{
    private BizRoleRepository $repository;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $registry->method('getManagerForClass')->willReturn($entityManager);

        $this->repository = new BizRoleRepository($registry);
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


    public function testRepositoryCanHandleBizRoleEntity(): void
    {
        $role = new BizRole();
        $role->setName('测试角色');
        $role->setTitle('测试角色标题');

        $this->assertEquals('测试角色', $role->getName());
        $this->assertEquals('测试角色标题', $role->getTitle());
    }

    public function testRepositoryClassMethods(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $docComment = $reflection->getDocComment();

        $this->assertStringContainsString('@method BizRole|null find(', $docComment);
        $this->assertStringContainsString('@method BizRole|null findOneBy(', $docComment);
        $this->assertStringContainsString('@method BizRole[]    findAll()', $docComment);
        $this->assertStringContainsString('@method BizRole[]    findBy(', $docComment);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $parentClass = $reflection->getParentClass();

        $this->assertEquals(ServiceEntityRepository::class, $parentClass->getName());
    }


    public function testRepositoryCanWorkWithBizRoleProperties(): void
    {
        $role = new BizRole();

        $role->setName('管理员');
        $role->setTitle('系统管理员');
        $role->setAdmin(true);
        $role->setValid(true);

        $this->assertEquals('管理员', $role->getName());
        $this->assertEquals('系统管理员', $role->getTitle());
        $this->assertTrue($role->isAdmin());
        $this->assertTrue($role->isValid());
    }


    public function testBizRoleStringRepresentation(): void
    {
        $role = new BizRole();
        $role->setName('管理员');
        $role->setTitle('系统管理员');

        // 初始状态没有ID时返回空字符串
        $this->assertEquals('', (string)$role);
    }

    public function testBizRolePermissions(): void
    {
        $role = new BizRole();
        $permissions = ['user.view', 'user.edit'];

        $role->setPermissions($permissions);
        $this->assertEquals($permissions, $role->getPermissions());
    }

}
