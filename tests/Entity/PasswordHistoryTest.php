<?php

namespace BizUserBundle\Tests\Entity;

use BizUserBundle\Entity\PasswordHistory;
use PHPUnit\Framework\TestCase;

class PasswordHistoryTest extends TestCase
{
    private PasswordHistory $passwordHistory;

    protected function setUp(): void
    {
        $this->passwordHistory = new PasswordHistory();
    }

    /**
     * 测试密码历史实体的基本属性 getter 和 setter
     */
    public function testGettersAndSetters(): void
    {
        // 测试密码密文
        $this->passwordHistory->setCiphertext('hashed_password_123');
        $this->assertEquals('hashed_password_123', $this->passwordHistory->getCiphertext());

        // 测试过期时间
        $expireTime = new \DateTime('2024-12-31 23:59:59');
        $this->passwordHistory->setExpireTime($expireTime);
        $this->assertSame($expireTime, $this->passwordHistory->getExpireTime());

        // 测试用户名
        $this->passwordHistory->setUsername('test_user');
        $this->assertEquals('test_user', $this->passwordHistory->getUsername());

        // 测试用户ID
        $this->passwordHistory->setUserId('12345');
        $this->assertEquals('12345', $this->passwordHistory->getUserId());

        // 测试创建IP
        $this->passwordHistory->setCreatedFromIp('192.168.1.100');
        $this->assertEquals('192.168.1.100', $this->passwordHistory->getCreatedFromIp());

        // 测试创建时间
        $createTime = new \DateTimeImmutable();
        $this->passwordHistory->setCreateTime($createTime);
        $this->assertSame($createTime, $this->passwordHistory->getCreateTime());
    }

    /**
     * 测试构造函数默认值
     */
    public function testConstructor_withDefaultValues(): void
    {
        $passwordHistory = new PasswordHistory();

        // 检查默认的needReset值
        $this->assertFalse($passwordHistory->isNeedReset());
    }

    /**
     * 测试构造函数指定needReset值
     */
    public function testConstructor_withNeedResetTrue(): void
    {
        $passwordHistory = new PasswordHistory(true);

        // 检查needReset值
        $this->assertTrue($passwordHistory->isNeedReset());
    }

    /**
     * 测试构造函数指定needReset为false
     */
    public function testConstructor_withNeedResetFalse(): void
    {
        $passwordHistory = new PasswordHistory(false);

        // 检查needReset值
        $this->assertFalse($passwordHistory->isNeedReset());
    }

    /**
     * 测试ID获取
     */
    public function testGetId(): void
    {
        // 由于ID是由Doctrine生成的，新创建的实体ID应该是null
        $this->assertNull($this->passwordHistory->getId());
    }

    /**
     * 测试密码密文设置
     */
    public function testSetCiphertext(): void
    {
        $result = $this->passwordHistory->setCiphertext('new_hashed_password');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查密码密文是否正确设置
        $this->assertEquals('new_hashed_password', $this->passwordHistory->getCiphertext());
    }

    /**
     * 测试密码密文设置为null
     */
    public function testSetCiphertext_withNull(): void
    {
        $result = $this->passwordHistory->setCiphertext(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查密码密文是否正确设置为null
        $this->assertNull($this->passwordHistory->getCiphertext());
    }

    /**
     * 测试过期时间设置
     */
    public function testSetExpireTime(): void
    {
        $expireTime = new \DateTime('2025-01-01 00:00:00');
        $result = $this->passwordHistory->setExpireTime($expireTime);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查过期时间是否正确设置
        $this->assertSame($expireTime, $this->passwordHistory->getExpireTime());
    }

    /**
     * 测试过期时间设置为null
     */
    public function testSetExpireTime_withNull(): void
    {
        $result = $this->passwordHistory->setExpireTime(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查过期时间是否正确设置为null
        $this->assertNull($this->passwordHistory->getExpireTime());
    }

    /**
     * 测试用户名设置
     */
    public function testSetUsername(): void
    {
        $result = $this->passwordHistory->setUsername('new_username');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查用户名是否正确设置
        $this->assertEquals('new_username', $this->passwordHistory->getUsername());
    }

    /**
     * 测试用户ID设置
     */
    public function testSetUserId(): void
    {
        $result = $this->passwordHistory->setUserId('98765');

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查用户ID是否正确设置
        $this->assertEquals('98765', $this->passwordHistory->getUserId());
    }

    /**
     * 测试用户ID设置为null
     */
    public function testSetUserId_withNull(): void
    {
        $result = $this->passwordHistory->setUserId(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查用户ID是否正确设置为null
        $this->assertNull($this->passwordHistory->getUserId());
    }

    /**
     * 测试创建时间设置
     */
    public function testSetCreateTime(): void
    {
        $createTime = new \DateTimeImmutable('2023-05-15 14:30:00');
        $result = $this->passwordHistory->setCreateTime($createTime);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查创建时间是否正确设置
        $this->assertSame($createTime, $this->passwordHistory->getCreateTime());
    }

    /**
     * 测试创建时间设置为null
     */
    public function testSetCreateTime_withNull(): void
    {
        $result = $this->passwordHistory->setCreateTime(null);

        // 检查返回值是自身（用于链式调用）
        $this->assertSame($this->passwordHistory, $result);

        // 检查创建时间是否正确设置为null
        $this->assertNull($this->passwordHistory->getCreateTime());
    }

    /**
     * 测试needReset标志
     */
    public function testIsNeedReset(): void
    {
        // 默认值测试
        $passwordHistory = new PasswordHistory(false);
        $this->assertFalse($passwordHistory->isNeedReset());

        // 设置为true
        $passwordHistory = new PasswordHistory(true);
        $this->assertTrue($passwordHistory->isNeedReset());
    }

    /**
     * 测试长密码密文
     */
    public function testSetCiphertext_withLongString(): void
    {
        $longCiphertext = str_repeat('a', 120); // 120字符长的密文
        $this->passwordHistory->setCiphertext($longCiphertext);
        $this->assertEquals($longCiphertext, $this->passwordHistory->getCiphertext());
    }

    /**
     * 测试空字符串密码密文
     */
    public function testSetCiphertext_withEmptyString(): void
    {
        $this->passwordHistory->setCiphertext('');
        $this->assertEquals('', $this->passwordHistory->getCiphertext());
    }

    /**
     * 测试特殊字符密码密文
     */
    public function testSetCiphertext_withSpecialCharacters(): void
    {
        $specialCiphertext = '$2y$10$ABC123def456GHI789jkl';
        $this->passwordHistory->setCiphertext($specialCiphertext);
        $this->assertEquals($specialCiphertext, $this->passwordHistory->getCiphertext());
    }

    /**
     * 测试IPv4地址
     */
    public function testSetCreatedFromIp_withIPv4(): void
    {
        $this->passwordHistory->setCreatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试IPv6地址
     */
    public function testSetCreatedFromIp_withIPv6(): void
    {
        $ipv6 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $this->passwordHistory->setCreatedFromIp($ipv6);
        $this->assertEquals($ipv6, $this->passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试IP地址设置为null
     */
    public function testSetCreatedFromIp_withNull(): void
    {
        $this->passwordHistory->setCreatedFromIp(null);
        $this->assertNull($this->passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试过期时间边界值
     */
    public function testExpireTime_withBoundaryValues(): void
    {
        // 测试最小时间
        $minTime = new \DateTime('1970-01-01 00:00:00');
        $this->passwordHistory->setExpireTime($minTime);
        $this->assertEquals($minTime, $this->passwordHistory->getExpireTime());

        // 测试最大时间
        $maxTime = new \DateTime('2038-01-19 03:14:07');
        $this->passwordHistory->setExpireTime($maxTime);
        $this->assertEquals($maxTime, $this->passwordHistory->getExpireTime());
    }

    /**
     * 测试用户名边界值
     */
    public function testUsername_withBoundaryValues(): void
    {
        // 测试空字符串用户名
        $this->passwordHistory->setUsername('');
        $this->assertEquals('', $this->passwordHistory->getUsername());

        // 测试最大长度用户名（假设最大50字符）
        $longUsername = str_repeat('a', 50);
        $this->passwordHistory->setUsername($longUsername);
        $this->assertEquals($longUsername, $this->passwordHistory->getUsername());
    }

    /**
     * 测试数字字符串用户ID
     */
    public function testUserId_withNumericString(): void
    {
        $this->passwordHistory->setUserId('123456789');
        $this->assertEquals('123456789', $this->passwordHistory->getUserId());
    }

    /**
     * 测试雪花ID格式的用户ID
     */
    public function testUserId_withSnowflakeId(): void
    {
        $snowflakeId = '1234567890123456789';
        $this->passwordHistory->setUserId($snowflakeId);
        $this->assertEquals($snowflakeId, $this->passwordHistory->getUserId());
    }
}
