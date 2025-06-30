<?php

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\UserAttributeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: UserAttributeRepository::class)]
#[ORM\Table(name: 'biz_user_attribute', options: ['comment' => '用户属性'])]
#[ORM\UniqueConstraint(name: 'biz_user_attribute_idx_uniq', columns: ['user_id', 'name'])]
class UserAttribute implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;


    #[Ignore]
    #[ORM\ManyToOne(targetEntity: BizUser::class, inversedBy: 'attributes')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?BizUser $user = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => '属性名'])]
    private string $name;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '值'])]
    private ?string $value = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;


    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return sprintf('UserAttribute %s (%s)', $this->getId(), $this->getName());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getUser(): ?BizUser
    {
        return $this->user;
    }

    public function setUser(?BizUser $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'value' => $this->getValue(),
        ];
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'remark' => $this->getRemark(),
        ];
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): static
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): static
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }
}
