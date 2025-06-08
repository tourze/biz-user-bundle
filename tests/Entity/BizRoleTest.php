<?php

namespace BizUserBundle\Tests\Entity;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\RoleEntityPermission;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class BizRoleTest extends TestCase
{
    private BizRole $role;

    protected function setUp(): void
    {
        $this->role = new BizRole();
    }

    /**
     * 测试角色实体的基本属性 getter 和 setter
     */
    public function testGettersAndSetters(): void
    {
        // 测试名称
        $this->role->setName('ROLE_ADMIN');
        $this->assertEquals('ROLE_ADMIN', $this->role->getName());

        // 测试标题
        $this->role->setTitle('系统管理员');
        $this->assertEquals('系统管理员', $this->role->getTitle());

        // 测试管理员标志
        $this->role->setAdmin(true);
        $this->assertTrue($this->role->isAdmin());

        // 测试有效状态
        $this->role->setValid(true);
        $this->assertTrue($this->role->isValid());

        // 测试权限数组
        $permissions = ['user_manage', 'role_manage'];
        $this->role->setPermissions($permissions);
        $this->assertEquals($permissions, $this->role->getPermissions());

        // 测试菜单JSON
        $menuJson = '{"menu": "data"}';
        $this->role->setMenuJson($menuJson);
        $this->assertEquals($menuJson, $this->role->getMenuJson());

        // 测试排除权限
        $excludePermissions = ['exclude_perm'];
        $this->role->setExcludePermissions($excludePermissions);
        $this->assertEquals($excludePermissions, $this->role->getExcludePermissions());

        // 测试层级角色
        $hierarchicalRoles = ['ROLE_USER'];
        $this->role->setHierarchicalRoles($hierarchicalRoles);
        $this->assertEquals($hierarchicalRoles, $this->role->getHierarchicalRoles());

        // 测试付费角色
        $this->role->setBillable(true);
        $this->assertTrue($this->role->isBillable());

        // 测试审计要求
        $this->role->setAuditRequired(true);
        $this->assertTrue($this->role->isAuditRequired());

        // 测试IP地址
        $this->role->setCreatedFromIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->role->getCreatedFromIp());

        $this->role->setUpdatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->role->getUpdatedFromIp());

        // 测试创建人和更新人
        $this->role->setCreatedBy('admin');
        $this->assertEquals('admin', $this->role->getCreatedBy());

        $this->role->setUpdatedBy('moderator');
        $this->assertEquals('moderator', $this->role->getUpdatedBy());

        // 测试时间字段
        $now = new \DateTime();
        $this->role->setCreateTime($now);
        $this->assertSame($now, $this->role->getCreateTime());

        $this->role->setUpdateTime($now);
        $this->assertSame($now, $this->role->getUpdateTime());
    }

    /**
     * 测试构造函数初始化集合属性
     */
    public function testConstructor(): void
    {
        // 测试 users 集合初始化
        $this->assertInstanceOf(ArrayCollection::class, $this->getObjectProperty($this->role, 'users'));
        $this->assertEmpty($this->getObjectProperty($this->role, 'users'));

        // 测试 dataPermissions 集合初始化
        $this->assertInstanceOf(ArrayCollection::class, $this->getObjectProperty($this->role, 'dataPermissions'));
        $this->assertEmpty($this->getObjectProperty($this->role, 'dataPermissions'));
    }

    /**
     * 测试对象字符串表示
     */
    public function testToString(): void
    {
        // 测试没有 ID 的情况
        $this->assertEquals('', (string) $this->role);

        // 设置 ID 和必要属性
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');

        $expected = '系统管理员(ROLE_ADMIN)';
        $this->assertEquals($expected, (string) $this->role);
    }

    /**
     * 测试添加用户
     */
    public function testAddUser(): void
    {
        $user = new BizUser();
        $user->setUsername('test_user');

        $result = $this->role->addUser($user);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->role, $result);

        // 检查用户是否已添加
        $users = $this->getObjectProperty($this->role, 'users');
        $this->assertCount(1, $users);
        $this->assertSame($user, $users->first());
    }

    /**
     * 测试移除用户
     */
    public function testRemoveUser(): void
    {
        // 先添加用户
        $user = new BizUser();
        $user->setUsername('test_user');
        $this->role->addUser($user);

        // 检查用户是否已添加
        $users = $this->getObjectProperty($this->role, 'users');
        $this->assertCount(1, $users);

        // 移除用户
        $result = $this->role->removeUser($user);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->role, $result);

        // 检查用户是否已移除
        $users = $this->getObjectProperty($this->role, 'users');
        $this->assertCount(0, $users);
    }

    /**
     * 测试获取用户集合
     */
    public function testGetUsers(): void
    {
        $users = $this->role->getUsers();

        $this->assertInstanceOf(ArrayCollection::class, $users);
        $this->assertEmpty($users);

        // 添加用户后再测试
        $user = new BizUser();
        $this->role->addUser($user);

        $users = $this->role->getUsers();
        $this->assertCount(1, $users);
        $this->assertSame($user, $users->first());
    }

    /**
     * 测试添加数据权限
     */
    public function testAddDataPermission(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setEntityClass('TestEntity');

        $result = $this->role->addDataPermission($permission);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->role, $result);

        // 检查权限是否已添加
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        $this->assertCount(1, $permissions);
        $this->assertSame($permission, $permissions->first());

        // 检查双向关系是否建立
        $this->assertSame($this->role, $permission->getRole());
    }

    /**
     * 测试移除数据权限
     */
    public function testRemoveDataPermission(): void
    {
        // 先添加权限
        $permission = new RoleEntityPermission();
        $permission->setEntityClass('TestEntity');
        $this->role->addDataPermission($permission);

        // 检查权限是否已添加
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        $this->assertCount(1, $permissions);

        // 移除权限
        $result = $this->role->removeDataPermission($permission);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->role, $result);

        // 检查权限是否已移除
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        $this->assertCount(0, $permissions);

        // 检查双向关系是否解除
        $this->assertNull($permission->getRole());
    }

    /**
     * 测试获取数据权限集合
     */
    public function testGetDataPermissions(): void
    {
        $permissions = $this->role->getDataPermissions();

        $this->assertInstanceOf(ArrayCollection::class, $permissions);
        $this->assertEmpty($permissions);

        // 添加权限后再测试
        $permission = new RoleEntityPermission();
        $this->role->addDataPermission($permission);

        $permissions = $this->role->getDataPermissions();
        $this->assertCount(1, $permissions);
        $this->assertSame($permission, $permissions->first());
    }

    /**
     * 测试权限列表渲染
     */
    public function testRenderPermissionList(): void
    {
        $permissions = ['user_manage', 'role_manage', 'system_config'];
        $this->role->setPermissions($permissions);

        $result = $this->role->renderPermissionList();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $index => $item) {
            $this->assertArrayHasKey('text', $item);
            $this->assertArrayHasKey('fontStyle', $item);
            $this->assertEquals($permissions[$index], $item['text']);
            $this->assertEquals(['fontSize' => '12px'], $item['fontStyle']);
        }
    }

    /**
     * 测试层级角色默认值处理
     */
    public function testGetHierarchicalRoles_withNullValue(): void
    {
        $this->role->setHierarchicalRoles(null);
        $this->assertEquals([], $this->role->getHierarchicalRoles());
    }

    /**
     * 测试普通数组表示
     */
    public function testRetrievePlainArray(): void
    {
        // 设置 ID
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');
        $this->role->setValid(true);
        $this->role->setHierarchicalRoles(['ROLE_USER']);

        $now = new \DateTime();
        $this->role->setCreateTime($now);
        $this->role->setUpdateTime($now);

        $result = $this->role->retrievePlainArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('hierarchicalRoles', $result);
        $this->assertArrayHasKey('createTime', $result);
        $this->assertArrayHasKey('updateTime', $result);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('ROLE_ADMIN', $result['name']);
        $this->assertEquals('系统管理员', $result['title']);
        $this->assertTrue($result['valid']);
        $this->assertEquals(['ROLE_USER'], $result['hierarchicalRoles']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $result['createTime']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $result['updateTime']);
    }

    /**
     * 测试管理员数组表示
     */
    public function testRetrieveAdminArray(): void
    {
        // 设置基础属性
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');
        $this->role->setValid(true);
        $this->role->setPermissions(['user_manage', 'role_manage']);

        $result = $this->role->retrieveAdminArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('permissions', $result);
        $this->assertArrayHasKey('userCount', $result);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('ROLE_ADMIN', $result['name']);
        $this->assertEquals('系统管理员', $result['title']);
        $this->assertTrue($result['valid']);
        $this->assertEquals(['user_manage', 'role_manage'], $result['permissions']);
        $this->assertEquals(0, $result['userCount']); // 没有用户时为0
    }

    /**
     * 辅助方法：获取对象的私有属性
     */
    private function getObjectProperty(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
