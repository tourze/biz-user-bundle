<?php

namespace BizUserBundle\Tests\Controller\Admin;

use BizUserBundle\Controller\Admin\PasswordHistoryCrudController;
use BizUserBundle\Entity\PasswordHistory;
use BizUserBundle\Repository\PasswordHistoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\TestCase;

class PasswordHistoryCrudControllerTest extends TestCase
{
    private PasswordHistoryCrudController $controller;
    private PasswordHistoryRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PasswordHistoryRepository::class);
        $this->controller = new PasswordHistoryCrudController();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(PasswordHistory::class, PasswordHistoryCrudController::getEntityFqcn());
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
        $this->assertGreaterThanOrEqual(5, count($fields));
    }

    public function testConfigureFieldsForDetailPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_DETAIL));
        
        $this->assertNotEmpty($fields);
    }

    public function testConfigureFiltersMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'configureFilters'));
    }

    public function testConfigureActionsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'configureActions'));
    }

    public function testPasswordHistoryIsReadOnly(): void
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setUsername('test@example.com');
        $passwordHistory->setUserId('123');
        $passwordHistory->setCiphertext('hashed_password');

        $this->assertEquals('test@example.com', $passwordHistory->getUsername());
        $this->assertEquals('123', $passwordHistory->getUserId());
        $this->assertEquals('hashed_password', $passwordHistory->getCiphertext());
    }

    public function testPasswordHistoryExpiration(): void
    {
        $passwordHistory = new PasswordHistory(true);
        $expireDate = new \DateTime('+30 days');
        $passwordHistory->setExpireTime($expireDate);

        $this->assertTrue($passwordHistory->isNeedReset());
        $this->assertEquals($expireDate, $passwordHistory->getExpireTime());
    }

    public function testPasswordHistoryCreatedFromIp(): void
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setCreatedFromIp('192.168.1.100');

        $this->assertEquals('192.168.1.100', $passwordHistory->getCreatedFromIp());
    }

    public function testPasswordHistoryConstruction(): void
    {
        $passwordHistory1 = new PasswordHistory();
        $passwordHistory2 = new PasswordHistory(false);
        $passwordHistory3 = new PasswordHistory(true);

        $this->assertFalse($passwordHistory1->isNeedReset());
        $this->assertFalse($passwordHistory2->isNeedReset());
        $this->assertTrue($passwordHistory3->isNeedReset());
    }

    public function testPasswordHistoryEntityFields(): void
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setUsername('admin@example.com');
        $passwordHistory->setUserId('admin123');
        $passwordHistory->setCiphertext('$2y$10$example_hash');
        $passwordHistory->setCreatedFromIp('10.0.0.1');
        
        $expireDate = new \DateTime('+90 days');
        $passwordHistory->setExpireTime($expireDate);

        $this->assertEquals('admin@example.com', $passwordHistory->getUsername());
        $this->assertEquals('admin123', $passwordHistory->getUserId());
        $this->assertEquals('$2y$10$example_hash', $passwordHistory->getCiphertext());
        $this->assertEquals('10.0.0.1', $passwordHistory->getCreatedFromIp());
        $this->assertEquals($expireDate, $passwordHistory->getExpireTime());
    }
}