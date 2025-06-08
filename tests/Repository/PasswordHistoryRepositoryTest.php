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

    public function testBasicRepositoryMethods(): void
    {
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'findOneBy'));
        $this->assertTrue(method_exists($this->repository, 'findAll'));
        $this->assertTrue(method_exists($this->repository, 'findBy'));
    }

    public function testCustomMethods(): void
    {
        $this->assertTrue(method_exists($this->repository, 'findLatestPasswordHistory'));
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

    public function testRepositoryMethodsWithEntityTypes(): void
    {
        $methods = ['find', 'findOneBy', 'findAll', 'findBy', 'findLatestPasswordHistory'];

        foreach ($methods as $methodName) {
            $this->assertTrue(
                method_exists($this->repository, $methodName),
                sprintf('方法 %s 应该存在', $methodName)
            );
        }
    }

    public function testPasswordHistoryEntityHasExpectedMethods(): void
    {
        $history = new PasswordHistory();

        $this->assertTrue(method_exists($history, 'setUsername'));
        $this->assertTrue(method_exists($history, 'getUsername'));
        $this->assertTrue(method_exists($history, 'setCiphertext'));
        $this->assertTrue(method_exists($history, 'getCiphertext'));
        $this->assertTrue(method_exists($history, 'setUserId'));
        $this->assertTrue(method_exists($history, 'getUserId'));
    }

    public function testPasswordHistoryProperties(): void
    {
        $history = new PasswordHistory();
        $history->setUsername('testuser');
        $history->setCiphertext('encrypted_password');

        $this->assertEquals('testuser', $history->getUsername());
        $this->assertEquals('encrypted_password', $history->getCiphertext());
    }

    public function testPasswordHistoryIpTracking(): void
    {
        $history = new PasswordHistory();

        $this->assertTrue(method_exists($history, 'setCreatedFromIp'));
        $this->assertTrue(method_exists($history, 'getCreatedFromIp'));
    }

    public function testPasswordHistoryTimestamps(): void
    {
        $history = new PasswordHistory();

        $this->assertTrue(method_exists($history, 'setCreateTime'));
        $this->assertTrue(method_exists($history, 'getCreateTime'));
    }

    public function testFindLatestPasswordHistoryMethodSignature(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('findLatestPasswordHistory');

        $this->assertEquals('findLatestPasswordHistory', $method->getName());
        $this->assertEquals(1, $method->getNumberOfParameters());

        $parameter = $method->getParameters()[0];
        $this->assertEquals('username', $parameter->getName());
        $this->assertEquals('string', $parameter->getType()->getName());
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
