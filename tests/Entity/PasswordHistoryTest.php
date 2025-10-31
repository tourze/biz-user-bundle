<?php

namespace BizUserBundle\Tests\Entity;

use BizUserBundle\Entity\PasswordHistory;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(PasswordHistory::class)]
final class PasswordHistoryTest extends AbstractEntityTestCase
{
    protected function createEntity(): PasswordHistory
    {
        return new PasswordHistory();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'ciphertext' => ['ciphertext', 'hashed_password_123'];
        yield 'expireTime' => ['expireTime', new \DateTimeImmutable('2024-12-31 23:59:59')];
        yield 'username' => ['username', 'test_user'];
        yield 'userId' => ['userId', '12345'];
        yield 'createdFromIp' => ['createdFromIp', '192.168.1.100'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
    }

    /**
     * 测试构造函数默认值
     */
    public function testConstructorWithDefaultValues(): void
    {
        $passwordHistory = new PasswordHistory();

        // 检查默认的needReset值
        $this->assertFalse($passwordHistory->isNeedReset());
    }

    /**
     * 测试构造函数指定needReset值
     */
    public function testConstructorWithNeedResetTrue(): void
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setNeedReset(true);

        // 检查needReset值
        $this->assertTrue($passwordHistory->isNeedReset());
    }

    /**
     * 测试构造函数指定needReset为false
     */
    public function testConstructorWithNeedResetFalse(): void
    {
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setNeedReset(false);

        // 检查needReset值
        $this->assertFalse($passwordHistory->isNeedReset());
    }

    /**
     * 测试ID获取
     */
    public function testGetId(): void
    {
        $passwordHistory = $this->createEntity();
        // 由于ID是由Doctrine生成的，新创建的实体ID应该是null
        $this->assertNull($passwordHistory->getId());
    }

    /**
     * 测试密码密文设置
     */
    public function testSetCiphertext(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCiphertext('new_hashed_password');

        // 检查密码密文是否正确设置
        $this->assertEquals('new_hashed_password', $passwordHistory->getCiphertext());
    }

    /**
     * 测试密码密文设置为null
     */
    public function testSetCiphertextWithNull(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCiphertext(null);

        // 检查密码密文是否正确设置为null
        $this->assertNull($passwordHistory->getCiphertext());
    }

    /**
     * 测试过期时间设置
     */
    public function testSetExpireTime(): void
    {
        $passwordHistory = $this->createEntity();
        $expireTime = new \DateTimeImmutable('2025-01-01 00:00:00');
        $passwordHistory->setExpireTime($expireTime);

        // 检查过期时间是否正确设置
        $this->assertSame($expireTime, $passwordHistory->getExpireTime());
    }

    /**
     * 测试过期时间设置为null
     */
    public function testSetExpireTimeWithNull(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setExpireTime(null);

        // 检查过期时间是否正确设置为null
        $this->assertNull($passwordHistory->getExpireTime());
    }

    /**
     * 测试用户名设置
     */
    public function testSetUsername(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setUsername('new_username');

        // 检查用户名是否正确设置
        $this->assertEquals('new_username', $passwordHistory->getUsername());
    }

    /**
     * 测试用户ID设置
     */
    public function testSetUserId(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setUserId('98765');

        // 检查用户ID是否正确设置
        $this->assertEquals('98765', $passwordHistory->getUserId());
    }

    /**
     * 测试用户ID设置为null
     */
    public function testSetUserIdWithNull(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setUserId(null);

        // 检查用户ID是否正确设置为null
        $this->assertNull($passwordHistory->getUserId());
    }

    /**
     * 测试创建时间设置
     */
    public function testSetCreateTime(): void
    {
        $passwordHistory = $this->createEntity();
        $createTime = new \DateTimeImmutable('2023-05-15 14:30:00');
        $passwordHistory->setCreateTime($createTime);

        // 检查创建时间是否正确设置
        $this->assertSame($createTime, $passwordHistory->getCreateTime());
    }

    /**
     * 测试创建时间设置为null
     */
    public function testSetCreateTimeWithNull(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCreateTime(null);

        // 检查创建时间是否正确设置为null
        $this->assertNull($passwordHistory->getCreateTime());
    }

    /**
     * 测试needReset标志
     */
    public function testIsNeedReset(): void
    {
        // 默认值测试
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setNeedReset(false);
        $this->assertFalse($passwordHistory->isNeedReset());

        // 设置为true
        $passwordHistory = new PasswordHistory();
        $passwordHistory->setNeedReset(true);
        $this->assertTrue($passwordHistory->isNeedReset());
    }

    /**
     * 测试长密码密文
     */
    public function testSetCiphertextWithLongString(): void
    {
        $passwordHistory = $this->createEntity();
        $longCiphertext = str_repeat('a', 120); // 120字符长的密文
        $passwordHistory->setCiphertext($longCiphertext);
        $this->assertEquals($longCiphertext, $passwordHistory->getCiphertext());
    }

    /**
     * 测试空字符串密码密文
     */
    public function testSetCiphertextWithEmptyString(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCiphertext('');
        $this->assertEquals('', $passwordHistory->getCiphertext());
    }

    /**
     * 测试特殊字符密码密文
     */
    public function testSetCiphertextWithSpecialCharacters(): void
    {
        $passwordHistory = $this->createEntity();
        $specialCiphertext = '$2y$10$ABC123def456GHI789jkl';
        $passwordHistory->setCiphertext($specialCiphertext);
        $this->assertEquals($specialCiphertext, $passwordHistory->getCiphertext());
    }

    /**
     * 测试IPv4地址
     */
    public function testSetCreatedFromIpWithIPv4(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCreatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试IPv6地址
     */
    public function testSetCreatedFromIpWithIPv6(): void
    {
        $passwordHistory = $this->createEntity();
        $ipv6 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $passwordHistory->setCreatedFromIp($ipv6);
        $this->assertEquals($ipv6, $passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试IP地址设置为null
     */
    public function testSetCreatedFromIpWithNull(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setCreatedFromIp(null);
        $this->assertNull($passwordHistory->getCreatedFromIp());
    }

    /**
     * 测试过期时间边界值
     */
    public function testExpireTimeWithBoundaryValues(): void
    {
        $passwordHistory = $this->createEntity();
        // 测试最小时间
        $minTime = new \DateTimeImmutable('1970-01-01 00:00:00');
        $passwordHistory->setExpireTime($minTime);
        $this->assertEquals($minTime, $passwordHistory->getExpireTime());

        // 测试最大时间
        $maxTime = new \DateTimeImmutable('2038-01-19 03:14:07');
        $passwordHistory->setExpireTime($maxTime);
        $this->assertEquals($maxTime, $passwordHistory->getExpireTime());
    }

    /**
     * 测试用户名边界值
     */
    public function testUsernameWithBoundaryValues(): void
    {
        $passwordHistory = $this->createEntity();
        // 测试空字符串用户名
        $passwordHistory->setUsername('');
        $this->assertEquals('', $passwordHistory->getUsername());

        // 测试最大长度用户名（假设最大50字符）
        $longUsername = str_repeat('a', 50);
        $passwordHistory->setUsername($longUsername);
        $this->assertEquals($longUsername, $passwordHistory->getUsername());
    }

    /**
     * 测试数字字符串用户ID
     */
    public function testUserIdWithNumericString(): void
    {
        $passwordHistory = $this->createEntity();
        $passwordHistory->setUserId('123456789');
        $this->assertEquals('123456789', $passwordHistory->getUserId());
    }

    /**
     * 测试雪花ID格式的用户ID
     */
    public function testUserIdWithSnowflakeId(): void
    {
        $passwordHistory = $this->createEntity();
        $snowflakeId = '1234567890123456789';
        $passwordHistory->setUserId($snowflakeId);
        $this->assertEquals($snowflakeId, $passwordHistory->getUserId());
    }
}
