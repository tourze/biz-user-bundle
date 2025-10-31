<?php

namespace BizUserBundle\Tests\Event;

use BizUserBundle\Event\FindUsersByIdentityEvent;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(FindUsersByIdentityEvent::class)]
final class FindUsersByIdentityEventTest extends AbstractEventTestCase
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
        // @phpstan-ignore-next-line PreferInterfaceStubTraitRule.createTestUser
        $user1 = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test_user_1';
            }
        };

        // @phpstan-ignore-next-line PreferInterfaceStubTraitRule.createTestUser
        $user2 = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_ADMIN'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test_user_2';
            }
        };

        /** @var ArrayCollection<int, UserInterface> $users */
        $users = new ArrayCollection([$user1, $user2]);

        $event = new FindUsersByIdentityEvent();
        $event->setUsers($users);

        $this->assertSame($users, $event->getUsers());
        $this->assertCount(2, $event->getUsers());
    }
}
