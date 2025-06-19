<?php

namespace BizUserBundle\Tests\Entity;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Entity\UserAttribute;
use PHPUnit\Framework\TestCase;

class UserAttributeTest extends TestCase
{
    private UserAttribute $attribute;

    protected function setUp(): void
    {
        $this->attribute = new UserAttribute();
    }

    /**
     * 测试用户属性实体的基本属性 getter 和 setter
     */
    public function testGettersAndSetters(): void
    {
        // 测试属性名
        $this->attribute->setName('test_attribute');
        $this->assertEquals('test_attribute', $this->attribute->getName());

        // 测试属性值
        $this->attribute->setValue('test_value');
        $this->assertEquals('test_value', $this->attribute->getValue());

        // 测试备注
        $this->attribute->setRemark('这是一个测试属性');
        $this->assertEquals('这是一个测试属性', $this->attribute->getRemark());

        // 测试关联用户
        $user = new BizUser();
        $user->setUsername('test_user');
        $this->attribute->setUser($user);
        $this->assertSame($user, $this->attribute->getUser());

        // 测试IP地址
        $this->attribute->setCreatedFromIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->attribute->getCreatedFromIp());

        $this->attribute->setUpdatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->attribute->getUpdatedFromIp());

        // 测试创建人和更新人
        $this->attribute->setCreatedBy('admin');
        $this->assertEquals('admin', $this->attribute->getCreatedBy());

        $this->attribute->setUpdatedBy('moderator');
        $this->assertEquals('moderator', $this->attribute->getUpdatedBy());

        // 测试时间字段
        $now = new \DateTimeImmutable();
        $this->attribute->setCreateTime($now);
        $this->assertSame($now, $this->attribute->getCreateTime());

        $this->attribute->setUpdateTime($now);
        $this->assertSame($now, $this->attribute->getUpdateTime());
    }

    /**
     * 测试ID获取
     */
    public function testGetId(): void
    {
        // 由于ID是由Doctrine生成的，新创建的实体ID应该是null
        $this->assertNull($this->attribute->getId());
    }

    /**
     * 测试用户关联设置
     */
    public function testSetUser(): void
    {
        $user = new BizUser();
        $user->setUsername('test_user');

        $result = $this->attribute->setUser($user);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查用户是否正确设置
        $this->assertSame($user, $this->attribute->getUser());
    }

    /**
     * 测试用户关联设置为null
     */
    public function testSetUser_withNull(): void
    {
        $result = $this->attribute->setUser(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查用户是否正确设置为null
        $this->assertNull($this->attribute->getUser());
    }

    /**
     * 测试属性名设置
     */
    public function testSetName(): void
    {
        $result = $this->attribute->setName('new_attribute');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查属性名是否正确设置
        $this->assertEquals('new_attribute', $this->attribute->getName());
    }

    /**
     * 测试属性值设置
     */
    public function testSetValue(): void
    {
        $result = $this->attribute->setValue('new_value');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查属性值是否正确设置
        $this->assertEquals('new_value', $this->attribute->getValue());
    }

    /**
     * 测试备注设置
     */
    public function testSetRemark(): void
    {
        $result = $this->attribute->setRemark('新的备注');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查备注是否正确设置
        $this->assertEquals('新的备注', $this->attribute->getRemark());
    }

    /**
     * 测试备注设置为null
     */
    public function testSetRemark_withNull(): void
    {
        $result = $this->attribute->setRemark(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查备注是否正确设置为null
        $this->assertNull($this->attribute->getRemark());
    }

    /**
     * 测试创建人设置
     */
    public function testSetCreatedBy(): void
    {
        $result = $this->attribute->setCreatedBy('admin_user');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查创建人是否正确设置
        $this->assertEquals('admin_user', $this->attribute->getCreatedBy());
    }

    /**
     * 测试更新人设置
     */
    public function testSetUpdatedBy(): void
    {
        $result = $this->attribute->setUpdatedBy('moderator_user');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查更新人是否正确设置
        $this->assertEquals('moderator_user', $this->attribute->getUpdatedBy());
    }

    /**
     * 测试创建IP设置
     */
    public function testSetCreatedFromIp(): void
    {
        $result = $this->attribute->setCreatedFromIp('10.0.0.1');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查创建IP是否正确设置
        $this->assertEquals('10.0.0.1', $this->attribute->getCreatedFromIp());
    }

    /**
     * 测试更新IP设置
     */
    public function testSetUpdatedFromIp(): void
    {
        $result = $this->attribute->setUpdatedFromIp('10.0.0.2');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->attribute, $result);

        // 检查更新IP是否正确设置
        $this->assertEquals('10.0.0.2', $this->attribute->getUpdatedFromIp());
    }

    /**
     * 测试API数组表示
     */
    public function testRetrieveApiArray(): void
    {
        // 设置ID（模拟数据库生成的ID）
        $reflection = new \ReflectionClass($this->attribute);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->attribute, '123456789');

        $this->attribute->setName('user_preference');
        $this->attribute->setValue('dark_theme');

        $result = $this->attribute->retrieveApiArray();
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);

        $this->assertEquals('123456789', $result['id']);
        $this->assertEquals('user_preference', $result['name']);
        $this->assertEquals('dark_theme', $result['value']);

        // 确保只包含API需要的字段
        $this->assertCount(3, $result);
    }

    /**
     * 测试管理员数组表示
     */
    public function testRetrieveAdminArray(): void
    {
        // 设置ID（模拟数据库生成的ID）
        $reflection = new \ReflectionClass($this->attribute);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->attribute, '123456789');

        $this->attribute->setName('user_preference');
        $this->attribute->setValue('dark_theme');
        $this->attribute->setRemark('用户主题偏好设置');

        $result = $this->attribute->retrieveAdminArray();
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('remark', $result);

        $this->assertEquals('123456789', $result['id']);
        $this->assertEquals('user_preference', $result['name']);
        $this->assertEquals('dark_theme', $result['value']);
        $this->assertEquals('用户主题偏好设置', $result['remark']);

        // 确保包含管理员需要的所有字段
        $this->assertCount(4, $result);
    }

    /**
     * 测试属性值为null的情况
     */
    public function testGetValue_withNullValue(): void
    {
        // 默认值应该是null
        $this->assertNull($this->attribute->getValue());

        // 由于setValue方法要求string类型，我们测试空字符串的情况
        $this->attribute->setValue('');
        $this->assertEquals('', $this->attribute->getValue());
    }

    /**
     * 测试空字符串属性值
     */
    public function testSetValue_withEmptyString(): void
    {
        $this->attribute->setValue('');
        $this->assertEquals('', $this->attribute->getValue());
    }

    /**
     * 测试长属性值
     */
    public function testSetValue_withLongString(): void
    {
        $longValue = str_repeat('这是一个很长的属性值', 100);
        $this->attribute->setValue($longValue);
        $this->assertEquals($longValue, $this->attribute->getValue());
    }

    /**
     * 测试特殊字符属性值
     */
    public function testSetValue_withSpecialCharacters(): void
    {
        $specialValue = 'test@#$%^&*()_+-={}[]|\\:";\'<>?,./`~';
        $this->attribute->setValue($specialValue);
        $this->assertEquals($specialValue, $this->attribute->getValue());
    }

    /**
     * 测试JSON格式属性值
     */
    public function testSetValue_withJsonString(): void
    {
        $jsonValue = '{"key": "value", "number": 123, "array": [1, 2, 3]}';
        $this->attribute->setValue($jsonValue);
        $this->assertEquals($jsonValue, $this->attribute->getValue());
    }

    /**
     * 测试时间设置
     */
    public function testTimeSetters(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2023-01-02 15:30:00');

        $this->attribute->setCreateTime($createTime);
        $this->attribute->setUpdateTime($updateTime);

        $this->assertSame($createTime, $this->attribute->getCreateTime());
        $this->assertSame($updateTime, $this->attribute->getUpdateTime());
    }

    /**
     * 测试时间设置为null
     */
    public function testTimeSetters_withNull(): void
    {
        $this->attribute->setCreateTime(null);
        $this->attribute->setUpdateTime(null);

        $this->assertNull($this->attribute->getCreateTime());
        $this->assertNull($this->attribute->getUpdateTime());
    }
}
