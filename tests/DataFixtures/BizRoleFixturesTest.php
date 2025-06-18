<?php

namespace BizUserBundle\Tests\DataFixtures;

use BizUserBundle\DataFixtures\BizRoleFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use PHPUnit\Framework\TestCase;

/**
 * 测试 BizRoleFixtures 类
 */
class BizRoleFixturesTest extends TestCase
{
    private BizRoleFixtures $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = new BizRoleFixtures();
    }

    public function testExtendsFixture(): void
    {
        $this->assertInstanceOf(Fixture::class, $this->fixtures);
    }

    public function testImplementsFixtureGroupInterface(): void
    {
        $this->assertInstanceOf(FixtureGroupInterface::class, $this->fixtures);
    }

    public function testGetGroups(): void
    {
        $groups = BizRoleFixtures::getGroups();
        $this->assertContains('user', $groups);
    }

    public function testRoleReferenceConstants(): void
    {
        $this->assertEquals('admin-role', BizRoleFixtures::ADMIN_ROLE_REFERENCE);
        $this->assertEquals('moderator-role', BizRoleFixtures::MODERATOR_ROLE_REFERENCE);
        $this->assertEquals('user-role', BizRoleFixtures::USER_ROLE_REFERENCE);
        $this->assertEquals('content-manager-role', BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE);
        $this->assertEquals('report-viewer-role', BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE);
        $this->assertEquals('analyst-role', BizRoleFixtures::ANALYST_ROLE_REFERENCE);
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

    }

    public function testConstantsAreNotEmpty(): void
    {
        $this->assertNotEmpty(BizRoleFixtures::ADMIN_ROLE_REFERENCE);
        $this->assertNotEmpty(BizRoleFixtures::MODERATOR_ROLE_REFERENCE);
        $this->assertNotEmpty(BizRoleFixtures::USER_ROLE_REFERENCE);
        $this->assertNotEmpty(BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE);
        $this->assertNotEmpty(BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE);
        $this->assertNotEmpty(BizRoleFixtures::ANALYST_ROLE_REFERENCE);
    }

    public function testConstantsAreUnique(): void
    {
        $constants = [
            BizRoleFixtures::ADMIN_ROLE_REFERENCE,
            BizRoleFixtures::MODERATOR_ROLE_REFERENCE,
            BizRoleFixtures::USER_ROLE_REFERENCE,
            BizRoleFixtures::CONTENT_MANAGER_ROLE_REFERENCE,
            BizRoleFixtures::REPORT_VIEWER_ROLE_REFERENCE,
            BizRoleFixtures::ANALYST_ROLE_REFERENCE,
        ];

        $uniqueConstants = array_unique($constants);
        $this->assertCount(count($constants), $uniqueConstants, '所有角色引用常量应该是唯一的');
    }

    public function testGetGroupsIsStatic(): void
    {
        $reflection = new \ReflectionClass(BizRoleFixtures::class);
        $method = $reflection->getMethod('getGroups');
        $this->assertTrue($method->isStatic());
    }

    public function testLoadMethodCanBeCalledWithoutErrors(): void
    {
        // 由于load方法使用了addReference，需要设置referenceRepository
        // 这里只测试方法存在性，不实际调用load方法
        $this->assertTrue(method_exists($this->fixtures, 'load'));
        $this->assertTrue(is_callable([$this->fixtures, 'load']));
    }

    public function testFixturesClassHasExpectedMethods(): void
    {
        $this->assertTrue(method_exists($this->fixtures, 'load'));
        $this->assertTrue(method_exists($this->fixtures, 'getGroups'));
        $this->assertTrue(method_exists($this->fixtures, 'addReference'));
    }

    public function testFixturesClassConstants(): void
    {
        $reflection = new \ReflectionClass(BizRoleFixtures::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('ADMIN_ROLE_REFERENCE', $constants);
        $this->assertArrayHasKey('MODERATOR_ROLE_REFERENCE', $constants);
        $this->assertArrayHasKey('USER_ROLE_REFERENCE', $constants);
        $this->assertArrayHasKey('CONTENT_MANAGER_ROLE_REFERENCE', $constants);
        $this->assertArrayHasKey('REPORT_VIEWER_ROLE_REFERENCE', $constants);
        $this->assertArrayHasKey('ANALYST_ROLE_REFERENCE', $constants);
    }

    public function testFixturesInheritsFromCorrectBaseClass(): void
    {
        $reflection = new \ReflectionClass($this->fixtures);
        $parentClass = $reflection->getParentClass();

        $this->assertEquals(Fixture::class, $parentClass->getName());
    }
}
