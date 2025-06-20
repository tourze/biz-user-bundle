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



    public function testUserAttributeProperties(): void
    {
        $attribute = new UserAttribute();
        $attribute->setName('test_name');
        $attribute->setValue('test_value');

        $this->assertEquals('test_name', $attribute->getName());
        $this->assertEquals('test_value', $attribute->getValue());
    }



}
