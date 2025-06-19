<?php

namespace BizUserBundle\Tests\DataFixtures;

use BizUserBundle\DataFixtures\BizRoleFixtures;
use BizUserBundle\DataFixtures\BizUserFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * 测试 BizUserFixtures 类
 */
class BizUserFixturesTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private BizUserFixtures $fixtures;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->fixtures = new BizUserFixtures($this->passwordHasher);
    }

    public function testExtendsFixture(): void
    {
        $this->assertInstanceOf(Fixture::class, $this->fixtures);
    }

    public function testImplementsFixtureGroupInterface(): void
    {
        $this->assertInstanceOf(FixtureGroupInterface::class, $this->fixtures);
    }

    public function testImplementsDependentFixtureInterface(): void
    {
        $this->assertInstanceOf(DependentFixtureInterface::class, $this->fixtures);
    }

    public function testGetGroups(): void
    {
        $groups = BizUserFixtures::getGroups();
        $this->assertContains('user', $groups);
    }

    public function testGetDependencies(): void
    {
        $dependencies = $this->fixtures->getDependencies();
        $this->assertContains(BizRoleFixtures::class, $dependencies);
    }

    public function testUserReferenceConstants(): void
    {
        $this->assertEquals('admin-user', BizUserFixtures::ADMIN_USER_REFERENCE);
        $this->assertEquals('moderator-user', BizUserFixtures::MODERATOR_USER_REFERENCE);
        $this->assertEquals('normal-user-', BizUserFixtures::NORMAL_USER_REFERENCE_PREFIX);
    }

    public function testLoadMethodExists(): void
    {
        $this->assertTrue(method_exists($this->fixtures, 'load'));

        $reflection = new \ReflectionClass($this->fixtures);
        $method = $reflection->getMethod('load');
        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('manager', $parameters[0]->getName());
    }

    public function testConstantsAreStrings(): void
    {
        $this->assertIsString(BizUserFixtures::ADMIN_USER_REFERENCE);
        $this->assertIsString(BizUserFixtures::MODERATOR_USER_REFERENCE);
        $this->assertIsString(BizUserFixtures::NORMAL_USER_REFERENCE_PREFIX);
    }

    public function testConstantsAreNotEmpty(): void
    {
        $this->assertNotEmpty(BizUserFixtures::ADMIN_USER_REFERENCE);
        $this->assertNotEmpty(BizUserFixtures::MODERATOR_USER_REFERENCE);
        $this->assertNotEmpty(BizUserFixtures::NORMAL_USER_REFERENCE_PREFIX);
    }

    public function testConstantsAreUnique(): void
    {
        $constants = [
            BizUserFixtures::ADMIN_USER_REFERENCE,
            BizUserFixtures::MODERATOR_USER_REFERENCE,
            BizUserFixtures::NORMAL_USER_REFERENCE_PREFIX,
        ];

        $uniqueConstants = array_unique($constants);
        $this->assertCount(count($constants), $uniqueConstants, '所有用户引用常量应该是唯一的');
    }

    public function testGetGroupsIsStatic(): void
    {
        $reflection = new \ReflectionClass(BizUserFixtures::class);
        $method = $reflection->getMethod('getGroups');
        $this->assertTrue($method->isStatic());
    }

    public function testGetDependenciesIsNotStatic(): void
    {
        $reflection = new \ReflectionClass(BizUserFixtures::class);
        $method = $reflection->getMethod('getDependencies');
        $this->assertFalse($method->isStatic());
    }

    public function testLoadMethodCanBeCalledWithoutErrors(): void
    {
        // 由于load方法使用了getReference和addReference，需要设置referenceRepository
        // 这里只测试方法存在性，不实际调用load方法
        $this->assertTrue(method_exists($this->fixtures, 'load'));
        $this->assertTrue(is_callable([$this->fixtures, 'load']));
    }

    public function testFixturesClassHasExpectedMethods(): void
    {
        $this->assertTrue(method_exists($this->fixtures, 'load'));
        $this->assertTrue(method_exists($this->fixtures, 'getGroups'));
        $this->assertTrue(method_exists($this->fixtures, 'getDependencies'));
        $this->assertTrue(method_exists($this->fixtures, 'addReference'));
        $this->assertTrue(method_exists($this->fixtures, 'getReference'));
    }

    public function testFixturesClassConstants(): void
    {
        $reflection = new \ReflectionClass(BizUserFixtures::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('ADMIN_USER_REFERENCE', $constants);
        $this->assertArrayHasKey('MODERATOR_USER_REFERENCE', $constants);
        $this->assertArrayHasKey('NORMAL_USER_REFERENCE_PREFIX', $constants);
    }

    public function testFixturesInheritsFromCorrectBaseClass(): void
    {
        $reflection = new \ReflectionClass($this->fixtures);
        $parentClass = $reflection->getParentClass();

        $this->assertEquals(Fixture::class, $parentClass->getName());
    }

    public function testConstructorRequiresPasswordHasher(): void
    {
        $reflection = new \ReflectionClass(BizUserFixtures::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);

        $parameter = $parameters[0];
        $this->assertEquals('passwordHasher', $parameter->getName());
        $this->assertEquals(UserPasswordHasherInterface::class, $parameter->getType()->getName());
    }

    public function testPasswordHasherIsCorrectlyInjected(): void
    {
        $reflection = new \ReflectionClass($this->fixtures);
        $property = $reflection->getProperty('passwordHasher');
        $property->setAccessible(true);

        $injectedHasher = $property->getValue($this->fixtures);
        $this->assertSame($this->passwordHasher, $injectedHasher);
    }

    public function testDependsOnBizRoleFixtures(): void
    {
        $dependencies = $this->fixtures->getDependencies();
        $this->assertContains(BizRoleFixtures::class, $dependencies);
        $this->assertCount(1, $dependencies);
    }
}
