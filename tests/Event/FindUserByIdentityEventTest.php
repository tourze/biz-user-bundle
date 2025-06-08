<?php

namespace BizUserBundle\Tests\Event;

use BizUserBundle\Event\FindUserByIdentityEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class FindUserByIdentityEventTest extends TestCase
{
    /**
     * 测试设置和获取 identity
     */
    public function testSetGetIdentity(): void
    {
        $event = new FindUserByIdentityEvent();
        $event->setIdentity('test_identity');

        $this->assertEquals('test_identity', $event->getIdentity());
    }

    /**
     * 测试设置和获取用户
     */
    public function testSetGetUser(): void
    {
        $user = $this->createMock(UserInterface::class);

        $event = new FindUserByIdentityEvent();
        $event->setUser($user);

        $this->assertSame($user, $event->getUser());
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues(): void
    {
        $event = new FindUserByIdentityEvent();

        // 由于 identity 是必需的字符串，先设置一个值
        $event->setIdentity('test');
        $this->assertEquals('test', $event->getIdentity());

        // 检查 user 属性默认为 null
        $this->assertNull($event->getUser());
    }
}
