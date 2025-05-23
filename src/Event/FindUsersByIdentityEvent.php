<?php

namespace BizUserBundle\Event;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FindUsersByIdentityEvent extends Event
{
    private string $identity;

    /**
     * @var Collection<UserInterface>
     */
    private Collection $users;

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): void
    {
        $this->identity = $identity;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }
}
