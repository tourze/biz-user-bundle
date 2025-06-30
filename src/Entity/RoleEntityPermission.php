<?php

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\RoleEntityPermissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'biz_data_permission', options: ['comment' => '角色实体数据权限'])]
#[ORM\UniqueConstraint(name: 'biz_data_permission_idx_uniq', columns: ['role_id', 'entity_class'])]
#[ORM\Entity(repositoryClass: RoleEntityPermissionRepository::class)]
class RoleEntityPermission implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'dataPermissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BizRole $role = null;

    #[Groups(groups: ['admin_curd'])]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体类名'])]
    private ?string $entityClass = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => 'WHERE语句'])]
    private ?string $statement = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;



    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getRole(): ?BizRole
    {
        return $this->role;
    }

    public function setRole(?BizRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getStatement(): ?string
    {
        return $this->statement;
    }

    public function setStatement(string $statement): self
    {
        $this->statement = $statement;

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

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return sprintf('RoleEntityPermission %s (%s)', $this->getId(), $this->getEntityClass());
    }
}
