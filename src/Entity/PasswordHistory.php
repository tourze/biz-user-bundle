<?php

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\PasswordHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;

/**
 * 密码修改记录
 */
#[ORM\Entity(repositoryClass: PasswordHistoryRepository::class, readOnly: true)]
#[ORM\Table(name: 'password_history', options: ['comment' => '密码修改记录'])]
class PasswordHistory implements \Stringable
{
    use CreateTimeAware;
    use SnowflakeKeyAware;

    #[ORM\Column(length: 130, nullable: true, options: ['comment' => '密码'])]
    private ?string $ciphertext = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否需要重置'])]
    private ?bool $needReset = null;

    #[IndexColumn]
    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '用户名'])]
    private ?string $username = null;

    #[IndexColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    public function __construct(bool $needReset = false)
    {
        $this->needReset = $needReset;
    }


    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return sprintf('PasswordHistory %s (%s)', $this->getId(), $this->getUsername());
    }

    public function getCiphertext(): ?string
    {
        return $this->ciphertext;
    }

    public function setCiphertext(?string $ciphertext): static
    {
        $this->ciphertext = $ciphertext;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): static
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    public function isNeedReset(): ?bool
    {
        return $this->needReset;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }
}
