<?php

namespace BizUserBundle\Tests\Controller\Admin;

use BizUserBundle\Controller\Admin\BizRoleCrudController;
use BizUserBundle\Entity\BizRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\TestCase;

class BizRoleCrudControllerTest extends TestCase
{
    private BizRoleCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new BizRoleCrudController();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(BizRole::class, BizRoleCrudController::getEntityFqcn());
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
    }

    public function testConfigureFieldsForEditPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_EDIT));
        
        $this->assertNotEmpty($fields);
    }

    public function testConfigureFieldsForDetailPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_DETAIL));
        
        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(10, count($fields));
    }






    public function testRoleEntityFields(): void
    {
        $role = new BizRole();
        $role->setName('ROLE_ADMIN');
        $role->setTitle('管理员');
        $role->setAdmin(true);
        $role->setValid(true);
        $role->setPermissions(['CREATE', 'READ', 'UPDATE', 'DELETE']);
        $role->setHierarchicalRoles(['ROLE_USER']);
        $role->setExcludePermissions(['SYSTEM_ACCESS']);

        $this->assertEquals('ROLE_ADMIN', $role->getName());
        $this->assertEquals('管理员', $role->getTitle());
        $this->assertTrue($role->isAdmin());
        $this->assertTrue($role->isValid());
        $this->assertEquals(['CREATE', 'READ', 'UPDATE', 'DELETE'], $role->getPermissions());
        $this->assertEquals(['ROLE_USER'], $role->getHierarchicalRoles());
        $this->assertEquals(['SYSTEM_ACCESS'], $role->getExcludePermissions());
    }
}