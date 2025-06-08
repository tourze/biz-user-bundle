<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\UserAttribute;
use BizUserBundle\Repository\UserAttributeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * 测试 UserAttributeRepository 类
 */
class UserAttributeRepositoryTest extends TestCase
{
    private UserAttributeRepository $repository;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $registry->method('getManagerForClass')->willReturn($entityManager);

        $this->repository = new UserAttributeRepository($registry);
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

    public function testRepositoryCanHandleUserAttributeEntity(): void
    {
        $attribute = new UserAttribute();
        $attribute->setName('test_name');
        $attribute->setValue('test_value');

        $this->assertEquals('test_name', $attribute->getName());
        $this->assertEquals('test_value', $attribute->getValue());
    }

    public function testRepositoryClassMethods(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $docComment = $reflection->getDocComment();

        $this->assertStringContainsString('@method UserAttribute|null find(', $docComment);
        $this->assertStringContainsString('@method UserAttribute|null findOneBy(', $docComment);
        $this->assertStringContainsString('@method UserAttribute[]    findAll()', $docComment);
        $this->assertStringContainsString('@method UserAttribute[]    findBy(', $docComment);
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

    public function testUserAttributeEntityHasExpectedMethods(): void
    {
        $attribute = new UserAttribute();

        $this->assertTrue(method_exists($attribute, 'setName'));
        $this->assertTrue(method_exists($attribute, 'getName'));
        $this->assertTrue(method_exists($attribute, 'setValue'));
        $this->assertTrue(method_exists($attribute, 'getValue'));
        $this->assertTrue(method_exists($attribute, 'setUser'));
        $this->assertTrue(method_exists($attribute, 'getUser'));
    }

    public function testUserAttributeProperties(): void
    {
        $attribute = new UserAttribute();
        $attribute->setName('test_name');
        $attribute->setValue('test_value');

        $this->assertEquals('test_name', $attribute->getName());
        $this->assertEquals('test_value', $attribute->getValue());
    }

    public function testUserAttributeArrayConversion(): void
    {
        $attribute = new UserAttribute();
        $attribute->setName('profile_name');
        $attribute->setValue('测试用户');

        $this->assertTrue(method_exists($attribute, 'retrieveApiArray'));
        $this->assertTrue(method_exists($attribute, 'retrieveAdminArray'));
    }

    public function testUserAttributeIpTracking(): void
    {
        $attribute = new UserAttribute();

        $this->assertTrue(method_exists($attribute, 'setCreatedFromIp'));
        $this->assertTrue(method_exists($attribute, 'getCreatedFromIp'));
        $this->assertTrue(method_exists($attribute, 'setUpdatedFromIp'));
        $this->assertTrue(method_exists($attribute, 'getUpdatedFromIp'));
    }

    public function testUserAttributeTimestamps(): void
    {
        $attribute = new UserAttribute();

        $this->assertTrue(method_exists($attribute, 'setCreateTime'));
        $this->assertTrue(method_exists($attribute, 'getCreateTime'));
        $this->assertTrue(method_exists($attribute, 'setUpdateTime'));
        $this->assertTrue(method_exists($attribute, 'getUpdateTime'));
    }
}
