<?php

namespace BizUserBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * 查找用户事件
 *
 * 有时间我们需要获得一个唯一的用户信息。一般来讲，只要有一个订阅者查找到了，我们就没必要继续
 */
class FindUserByIdentityEvent extends Event
{
    private string $identity;

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): void
    {
        $this->identity = $identity;
    }

    private ?UserInterface $user = null;

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }
}
