<?php

namespace BizUserBundle\Tests\Event;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Event\FindUsersByIdentityEvent;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class FindUsersByIdentityEventTest extends TestCase
{
    /**
     * 测试设置和获取 identity
     */
    public function testSetGetIdentity(): void
    {
        $event = new FindUsersByIdentityEvent();
        $event->setIdentity('test_identity');

        $this->assertEquals('test_identity', $event->getIdentity());
    }

    /**
     * 测试设置和获取用户集合
     */
    public function testSetGetUsers(): void
    {
        $user1 = new BizUser();
        $user1->setUsername('user1');

        $user2 = new BizUser();
        $user2->setUsername('user2');

        $users = new ArrayCollection([$user1, $user2]);

        $event = new FindUsersByIdentityEvent();
        $event->setUsers($users);

        $this->assertSame($users, $event->getUsers());
        $this->assertCount(2, $event->getUsers());
    }
}
