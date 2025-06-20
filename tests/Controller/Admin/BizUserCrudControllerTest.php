<?php

namespace BizUserBundle\Tests\Controller\Admin;

use BizUserBundle\Controller\Admin\BizUserCrudController;
use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\BizUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BizUserCrudControllerTest extends TestCase
{
    private BizUserCrudController $controller;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->controller = new BizUserCrudController($this->passwordHasher);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(BizUser::class, BizUserCrudController::getEntityFqcn());
    }

    public function testConfigureCrud(): void
    {
        $crud = $this->createMock(Crud::class);
        $result = $this->controller->configureCrud($crud);
        
        $this->assertInstanceOf(Crud::class, $result);
    }

    public function testConfigureFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));
        
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(5, count($fields));
    }

    public function testConfigureFieldsForNewPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_NEW));
        
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(10, count($fields));
    }

    public function testConfigureFieldsForEditPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_EDIT));
        
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(10, count($fields));
    }

    public function testConfigureFieldsForDetailPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_DETAIL));
        
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(15, count($fields));
    }

    public function testPasswordEncoding(): void
    {
        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        $user->setPlainPassword('password123');
        
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'password123')
            ->willReturn('hashed_password');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);
        $method->invoke($this->controller, $user);
        
        $this->assertEquals('hashed_password', $user->getPasswordHash());
        $this->assertNull($user->getPlainPassword());
    }

    public function testPasswordEncodingWithoutPassword(): void
    {
        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        
        $this->passwordHasher->expects($this->never())
            ->method('hashPassword');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);
        $method->invoke($this->controller, $user);
        
        $this->assertNull($user->getPasswordHash());
    }

    public function testPasswordUpdate(): void
    {
        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        $user->setPlainPassword('newpassword123');
        
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'newpassword123')
            ->willReturn('new_hashed_password');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);
        $method->invoke($this->controller, $user);
        
        $this->assertEquals('new_hashed_password', $user->getPasswordHash());
        $this->assertNull($user->getPlainPassword());
    }

    public function testPasswordUpdateWithoutChange(): void
    {
        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        $user->setPasswordHash('existing_hash');
        
        $this->passwordHasher->expects($this->never())
            ->method('hashPassword');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('encodePassword');
        $method->setAccessible(true);
        $method->invoke($this->controller, $user);
        
        $this->assertEquals('existing_hash', $user->getPasswordHash());
    }


    public function testUserEntityFields(): void
    {
        $role = new BizRole();
        $role->setName('ROLE_USER');
        $role->setTitle('普通用户');

        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('测试用户');
        $user->setEmail('test@example.com');
        $user->setMobile('13800138000');
        $user->setType('customer');
        $user->setIdentity('test_identity');
        $user->setValid(true);
        $user->setGender('male');
        $user->setProvinceName('北京市');
        $user->setCityName('北京市');
        $user->setAreaName('朝阳区');
        $user->setAddress('某某街道123号');
        $user->setRemark('测试用户备注');
        $user->addAssignRole($role);

        $this->assertEquals('test@example.com', $user->getUsername());
        $this->assertEquals('测试用户', $user->getNickName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('13800138000', $user->getMobile());
        $this->assertEquals('customer', $user->getType());
        $this->assertEquals('test_identity', $user->getIdentity());
        $this->assertTrue($user->isValid());
        $this->assertEquals('male', $user->getGender());
        $this->assertEquals('北京市', $user->getProvinceName());
        $this->assertEquals('北京市', $user->getCityName());
        $this->assertEquals('朝阳区', $user->getAreaName());
        $this->assertEquals('某某街道123号', $user->getAddress());
        $this->assertEquals('测试用户备注', $user->getRemark());
        $this->assertCount(1, $user->getAssignRoles());
    }

    public function testUserPasswordHandling(): void
    {
        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        $user->setPlainPassword('test123');
        
        $this->assertEquals('test123', $user->getPlainPassword());
        
        $user->setPasswordHash('hashed_password');
        $this->assertEquals('hashed_password', $user->getPasswordHash());
        $this->assertEquals('hashed_password', $user->getPassword());
        
        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
        $this->assertEquals('hashed_password', $user->getPasswordHash());
    }

    public function testUserRoles(): void
    {
        $role1 = new BizRole();
        $role1->setName('ROLE_USER');
        $role1->setTitle('普通用户');
        $role1->setValid(true);

        $role2 = new BizRole();
        $role2->setName('ROLE_ADMIN');
        $role2->setTitle('管理员');
        $role2->setValid(true);

        $user = new BizUser();
        $user->setUsername('test@example.com');
        $user->setNickName('Test User');
        $user->addAssignRole($role1);
        $user->addAssignRole($role2);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }
}