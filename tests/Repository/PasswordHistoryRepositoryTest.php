<?php

namespace BizUserBundle\Tests\Repository;

use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Repository\PasswordHistoryRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * 测试 PasswordHistoryRepository 类
 */
class PasswordHistoryRepositoryTest extends TestCase
{
    private PasswordHistoryRepository $repository;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $registry->method('getManagerForClass')->willReturn($entityManager);

        $this->repository = new PasswordHistoryRepository($registry);
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



    public function testRepositoryCanHandlePasswordHistoryEntity(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('testuser');
        $history->setCiphertext('encrypted_password');

        $this->assertEquals('testuser', $history->getUsername());
        $this->assertEquals('encrypted_password', $history->getCiphertext());
    }

    public function testRepositoryClassMethods(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $docComment = $reflection->getDocComment();

        $this->assertStringContainsString('@method PasswordHistory|null find(', $docComment);
        $this->assertStringContainsString('@method PasswordHistory|null findOneBy(', $docComment);
        $this->assertStringContainsString('@method PasswordHistory[]    findAll()', $docComment);
        $this->assertStringContainsString('@method PasswordHistory[]    findBy(', $docComment);
    }

    public function testEntityClassConstant(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $parentClass = $reflection->getParentClass();

        $this->assertEquals(ServiceEntityRepository::class, $parentClass->getName());
    }



    public function testPasswordHistoryProperties(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('testuser');
        $history->setCiphertext('encrypted_password');

        $this->assertEquals('testuser', $history->getUsername());
        $this->assertEquals('encrypted_password', $history->getCiphertext());
    }



    public function testFindLatestPasswordHistoryMethodSignature(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('findLatestPasswordHistory');

        $this->assertEquals('findLatestPasswordHistory', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());

        $parameter = $method->getParameters()[0];
        $this->assertEquals('username', $parameter->getName());
        $type = $parameter->getType();
        $this->assertNotNull($type);
        $this->assertEquals('string', (string) $type);
    }

    public function testRepositoryHasAutoconfigureAttribute(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $attributes = $reflection->getAttributes();

        $hasAutoconfigure = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Autoconfigure')) {
                $hasAutoconfigure = true;
                break;
            }
        }

        $this->assertTrue($hasAutoconfigure, 'PasswordHistoryRepository应该有Autoconfigure属性');
    }
}
