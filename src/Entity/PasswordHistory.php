<?php

declare(strict_types=1);

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\PasswordHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
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
    use CreatedFromIpAware;
    use SnowflakeKeyAware;

    #[Assert\Length(max: 130)]
    #[ORM\Column(length: 130, nullable: true, options: ['comment' => '密码'])]
    private ?string $ciphertext = null;

    #[Assert\Type(type: 'DateTimeImmutable')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[IndexColumn]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '用户名'])]
    private ?string $username = null;

    #[IndexColumn]
    #[Assert\Length(max: 191)]
    #[ORM\Column(nullable: true, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '是否需要重置'])]
    private bool $needReset = false;

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return sprintf('PasswordHistory %s (%s)', $this->getId(), $this->getUsername());
    }

    public function getCiphertext(): ?string
    {
        return $this->ciphertext;
    }

    public function setCiphertext(?string $ciphertext): void
    {
        $this->ciphertext = $ciphertext;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function isNeedReset(): bool
    {
        return $this->needReset;
    }

    public function setNeedReset(bool $needReset): void
    {
        $this->needReset = $needReset;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }
}
