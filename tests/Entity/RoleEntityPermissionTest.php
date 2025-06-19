<?php

namespace BizUserBundle\Tests\Entity;

use BizUserBundle\Entity\BizRole;
use BizUserBundle\Entity\RoleEntityPermission;
use PHPUnit\Framework\TestCase;

class RoleEntityPermissionTest extends TestCase
{
    private RoleEntityPermission $permission;

    protected function setUp(): void
    {
        $this->permission = new RoleEntityPermission();
    }

    /**
     * 测试角色实体权限的基本属性 getter 和 setter
     */
    public function testGettersAndSetters(): void
    {
        // 测试实体类名
        $this->permission->setEntityClass('App\\Entity\\User');
        $this->assertEquals('App\\Entity\\User', $this->permission->getEntityClass());

        // 测试SQL语句
        $statement = "user_id = :current_user_id AND status = 'active'";
        $this->permission->setStatement($statement);
        $this->assertEquals($statement, $this->permission->getStatement());

        // 测试备注
        $this->permission->setRemark('用户只能查看自己的数据');
        $this->assertEquals('用户只能查看自己的数据', $this->permission->getRemark());

        // 测试有效状态
        $this->permission->setValid(true);
        $this->assertTrue($this->permission->isValid());

        // 测试角色关联
        $role = new BizRole();
        $role->setName('ROLE_USER');
        $this->permission->setRole($role);
        $this->assertSame($role, $this->permission->getRole());

        // 测试创建人和更新人
        $this->permission->setCreatedBy('admin');
        $this->assertEquals('admin', $this->permission->getCreatedBy());

        $this->permission->setUpdatedBy('moderator');
        $this->assertEquals('moderator', $this->permission->getUpdatedBy());

        // 测试时间字段
        $now = new \DateTimeImmutable();
        $this->permission->setCreateTime($now);
        $this->assertSame($now, $this->permission->getCreateTime());

        $this->permission->setUpdateTime($now);
        $this->assertSame($now, $this->permission->getUpdateTime());
    }

    /**
     * 测试ID获取
     */
    public function testGetId(): void
    {
        // 由于ID是由Doctrine生成的，新创建的实体ID应该是null
        $this->assertNull($this->permission->getId());
    }

    /**
     * 测试实体类名设置
     */
    public function testSetEntityClass(): void
    {
        $result = $this->permission->setEntityClass('App\\Entity\\Product');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查实体类名是否正确设置
        $this->assertEquals('App\\Entity\\Product', $this->permission->getEntityClass());
    }

    /**
     * 测试SQL语句设置
     */
    public function testSetStatement(): void
    {
        $statement = "department_id IN (SELECT id FROM departments WHERE manager_id = :current_user_id)";
        $result = $this->permission->setStatement($statement);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查SQL语句是否正确设置
        $this->assertEquals($statement, $this->permission->getStatement());
    }

    /**
     * 测试备注设置
     */
    public function testSetRemark(): void
    {
        $result = $this->permission->setRemark('管理员可以查看所有数据');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查备注是否正确设置
        $this->assertEquals('管理员可以查看所有数据', $this->permission->getRemark());
    }

    /**
     * 测试备注设置为null
     */
    public function testSetRemark_withNull(): void
    {
        $result = $this->permission->setRemark(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查备注是否正确设置为null
        $this->assertNull($this->permission->getRemark());
    }

    /**
     * 测试有效状态设置
     */
    public function testSetValid(): void
    {
        $result = $this->permission->setValid(false);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查有效状态是否正确设置
        $this->assertFalse($this->permission->isValid());
    }

    /**
     * 测试角色设置
     */
    public function testSetRole(): void
    {
        $role = new BizRole();
        $role->setName('ROLE_ADMIN');
        $role->setTitle('系统管理员');

        $result = $this->permission->setRole($role);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查角色是否正确设置
        $this->assertSame($role, $this->permission->getRole());
    }

    /**
     * 测试角色设置为null
     */
    public function testSetRole_withNull(): void
    {
        $result = $this->permission->setRole(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查角色是否正确设置为null
        $this->assertNull($this->permission->getRole());
    }

    /**
     * 测试创建人设置
     */
    public function testSetCreatedBy(): void
    {
        $result = $this->permission->setCreatedBy('system_admin');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查创建人是否正确设置
        $this->assertEquals('system_admin', $this->permission->getCreatedBy());
    }

    /**
     * 测试更新人设置
     */
    public function testSetUpdatedBy(): void
    {
        $result = $this->permission->setUpdatedBy('data_admin');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->permission, $result);

        // 检查更新人是否正确设置
        $this->assertEquals('data_admin', $this->permission->getUpdatedBy());
    }

    /**
     * 测试创建时间设置
     */
    public function testSetCreateTime(): void
    {
        $createTime = new \DateTimeImmutable('2023-06-01 09:00:00');
        $this->permission->setCreateTime($createTime);

        $this->assertSame($createTime, $this->permission->getCreateTime());
    }

    /**
     * 测试更新时间设置
     */
    public function testSetUpdateTime(): void
    {
        $updateTime = new \DateTimeImmutable('2023-06-02 10:30:00');
        $this->permission->setUpdateTime($updateTime);

        $this->assertSame($updateTime, $this->permission->getUpdateTime());
    }

    /**
     * 测试时间设置为null
     */
    public function testTimeSetters_withNull(): void
    {
        $this->permission->setCreateTime(null);
        $this->permission->setUpdateTime(null);

        $this->assertNull($this->permission->getCreateTime());
        $this->assertNull($this->permission->getUpdateTime());
    }

    /**
     * 测试复杂SQL语句
     */
    public function testSetStatement_withComplexSQL(): void
    {
        $complexStatement = "EXISTS (SELECT 1 FROM user_permissions up " .
            "WHERE up.user_id = :current_user_id " .
            "AND up.entity_type = 'Product' " .
            "AND up.entity_id = id " .
            "AND up.permission_type = 'read')";

        $this->permission->setStatement($complexStatement);
        $this->assertEquals($complexStatement, $this->permission->getStatement());
    }

    /**
     * 测试简单SQL语句
     */
    public function testSetStatement_withSimpleSQL(): void
    {
        $simpleStatement = "1=1"; // 允许所有数据
        $this->permission->setStatement($simpleStatement);
        $this->assertEquals($simpleStatement, $this->permission->getStatement());
    }

    /**
     * 测试带参数的SQL语句
     */
    public function testSetStatement_withParameters(): void
    {
        $parameterizedStatement = "created_by = :current_user_id OR assigned_to = :current_user_id";
        $this->permission->setStatement($parameterizedStatement);
        $this->assertEquals($parameterizedStatement, $this->permission->getStatement());
    }

    /**
     * 测试不同实体类名格式
     */
    public function testSetEntityClass_withDifferentFormats(): void
    {
        // 测试完整命名空间格式
        $this->permission->setEntityClass('App\\Entity\\UserProfile');
        $this->assertEquals('App\\Entity\\UserProfile', $this->permission->getEntityClass());

        // 测试短格式
        $this->permission->setEntityClass('Order');
        $this->assertEquals('Order', $this->permission->getEntityClass());

        // 测试Bundle格式
        $this->permission->setEntityClass('UserBundle\\Entity\\User');
        $this->assertEquals('UserBundle\\Entity\\User', $this->permission->getEntityClass());
    }

    /**
     * 测试长备注
     */
    public function testSetRemark_withLongText(): void
    {
        $longRemark = str_repeat('这是一个很长的备注说明，用于描述数据权限的详细规则和使用场景。', 10);
        $this->permission->setRemark($longRemark);
        $this->assertEquals($longRemark, $this->permission->getRemark());
    }

    /**
     * 测试空字符串备注
     */
    public function testSetRemark_withEmptyString(): void
    {
        $this->permission->setRemark('');
        $this->assertEquals('', $this->permission->getRemark());
    }

    /**
     * 测试特殊字符备注
     */
    public function testSetRemark_withSpecialCharacters(): void
    {
        $specialRemark = "用户权限：读取(R)、写入(W)、删除(D) - 适用于 <User> 实体 & 相关表";
        $this->permission->setRemark($specialRemark);
        $this->assertEquals($specialRemark, $this->permission->getRemark());
    }

    /**
     * 测试有效状态的边界值
     */
    public function testValid_withBoundaryValues(): void
    {
        // 测试默认值
        $permission = new RoleEntityPermission();
        $this->assertFalse($permission->isValid());

        // 测试设置为true
        $permission->setValid(true);
        $this->assertTrue($permission->isValid());

        // 测试设置为false
        $permission->setValid(false);
        $this->assertFalse($permission->isValid());
    }

    /**
     * 测试角色关联的双向关系
     */
    public function testRoleAssociation_bidirectional(): void
    {
        $role = new BizRole();
        $role->setName('ROLE_MANAGER');

        // 设置角色
        $this->permission->setRole($role);
        $this->assertSame($role, $this->permission->getRole());

        // 检查角色的数据权限集合是否包含此权限
        $role->addDataPermission($this->permission);
        $this->assertTrue($role->getDataPermissions()->contains($this->permission));
    }

    /**
     * 测试创建人和更新人设置为null
     */
    public function testUserSetters_withNull(): void
    {
        $this->permission->setCreatedBy(null);
        $this->permission->setUpdatedBy(null);

        $this->assertNull($this->permission->getCreatedBy());
        $this->assertNull($this->permission->getUpdatedBy());
    }

    /**
     * 测试SQL注入防护场景的语句
     */
    public function testSetStatement_withSecurityConsiderations(): void
    {
        // 测试带引号的安全语句
        $secureStatement = "status = 'active' AND organization_id = :user_org_id";
        $this->permission->setStatement($secureStatement);
        $this->assertEquals($secureStatement, $this->permission->getStatement());

        // 测试参数化查询
        $parameterizedStatement = "created_at >= :start_date AND created_at <= :end_date";
        $this->permission->setStatement($parameterizedStatement);
        $this->assertEquals($parameterizedStatement, $this->permission->getStatement());
    }
}
