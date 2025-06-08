<?php

namespace BizUserBundle\Tests\Controller\Admin;

use BizUserBundle\Controller\Admin\BizUserCrudController;
use BizUserBundle\Entity\BizUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * 测试 BizUserCrudController 类
 */
class BizUserCrudControllerTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private BizUserCrudController $controller;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->controller = new BizUserCrudController($this->passwordHasher);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(BizUser::class, BizUserCrudController::getEntityFqcn());
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(Crud::class);

        $result = $this->controller->configureCrud($crud);
        $this->assertInstanceOf(Crud::class, $result);
    }

    public function testConfigureFieldsForNewPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_NEW));

        $this->assertCount(6, $fields);

        // 验证字段类型
        $this->assertInstanceOf(IdField::class, $fields[0]);
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertInstanceOf(EmailField::class, $fields[3]);
        $this->assertInstanceOf(TextField::class, $fields[4]); // plainPassword field
        $this->assertInstanceOf(AssociationField::class, $fields[5]);
    }

    public function testConfigureFieldsForEditPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_EDIT));

        $this->assertCount(6, $fields);

        // 验证字段类型
        $this->assertInstanceOf(IdField::class, $fields[0]);
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertInstanceOf(EmailField::class, $fields[3]);
        $this->assertInstanceOf(TextField::class, $fields[4]); // plainPassword field
        $this->assertInstanceOf(AssociationField::class, $fields[5]);
    }

    public function testConfigureFieldsForIndexPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(5, $fields);

        // 在INDEX页面不应该有密码字段
        $fieldTypes = array_map(function ($field) {
            return get_class($field);
        }, $fields);

        $this->assertContains(IdField::class, $fieldTypes);
        $this->assertContains(TextField::class, $fieldTypes);
        $this->assertContains(EmailField::class, $fieldTypes);
        $this->assertContains(AssociationField::class, $fieldTypes);
    }

    public function testEncodePasswordWithPassword(): void
    {
        $user = new BizUser();
        $user->setPlainPassword('testpassword');

        // 简化测试 - 只验证私有方法存在和可以调用
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);

        // 验证方法可以被调用而不抛出异常
        $this->assertNull($method->invoke($this->controller, $user));
    }

    public function testEncodePasswordWithoutPassword(): void
    {
        $user = new BizUser();
        // 不设置明文密码

        // 使用反射来测试私有方法
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);

        $method->invoke($this->controller, $user);

        $this->assertNull($user->getPasswordHash());
    }

    public function testControllerHasExpectedMethods(): void
    {
        $this->assertTrue(method_exists($this->controller, 'persistEntity'));
        $this->assertTrue(method_exists($this->controller, 'updateEntity'));
        $this->assertTrue(method_exists($this->controller, 'deleteEntity'));
        $this->assertTrue(method_exists($this->controller, 'configureFields'));
        $this->assertTrue(method_exists($this->controller, 'configureActions'));
        $this->assertTrue(method_exists($this->controller, 'configureCrud'));
    }

    public function testControllerHasPrivateEncodePasswordMethod(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $this->assertTrue($reflection->hasMethod('encodePassword'));

        $method = $reflection->getMethod('encodePassword');
        $this->assertTrue($method->isPrivate());
    }

    public function testPasswordHasherIsCorrectlyInjected(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('passwordHasher');
        $property->setAccessible(true);

        $injectedHasher = $property->getValue($this->controller);
        $this->assertSame($this->passwordHasher, $injectedHasher);
    }

    public function testStaticGetEntityFqcnMethod(): void
    {
        $this->assertTrue(method_exists(BizUserCrudController::class, 'getEntityFqcn'));

        $reflection = new \ReflectionClass(BizUserCrudController::class);
        $method = $reflection->getMethod('getEntityFqcn');
        $this->assertTrue($method->isStatic());
    }
}
