<?php

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\BizRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: BizRoleRepository::class)]
#[ORM\Table(name: 'biz_role', options: ['comment' => '系统角色'])]
class BizRole implements \Stringable, PlainArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '标题'])]
    private ?string $title = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否系统管理员'])]
    private ?bool $admin = false;

    /**
     * @var array<string>
     */
    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '拥有权限'])]
    private array $permissions = [];

    /**
     * @var Collection<int, BizUser>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: BizUser::class, mappedBy: 'assignRoles', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => 1, 'comment' => '是否有效'])]
    private ?bool $valid = true;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '自定义菜单JSON'])]
    private ?string $menuJson = '';

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '要排除的权限'])]
    private array $excludePermissions = [];

    /**
     * @var array<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '继承角色'])]
    private ?array $hierarchicalRoles = ['ROLE_OPERATOR'];

    /**
     * @var Collection<int, RoleEntityPermission>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: RoleEntityPermission::class, mappedBy: 'role')]
    private Collection $dataPermissions;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;


    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->dataPermissions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === 0) {
            return '';
        }

        return sprintf('%s(%s)', $this->getTitle(), $this->getName());
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array<string>|null $permissions
     */
    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions ?? [];

        return $this;
    }

    /**
     * @return Collection<int, BizUser>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(BizUser $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addAssignRole($this);
        }

        return $this;
    }

    public function removeUser(BizUser $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeAssignRole($this);
        }

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(?bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getMenuJson(): ?string
    {
        return $this->menuJson;
    }

    public function setMenuJson(?string $menuJson): self
    {
        $this->menuJson = $menuJson;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getExcludePermissions(): array
    {
        return $this->excludePermissions;
    }

    /**
     * @param array<string>|null $excludePermissions
     */
    public function setExcludePermissions(?array $excludePermissions): self
    {
        $this->excludePermissions = $excludePermissions ?? [];

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function renderPermissionList(): array
    {
        $res = [];
        foreach ($this->getPermissions() as $permission) {
            $res[] = [
                'text' => $permission,
                'fontStyle' => ['fontSize' => '12px'],
            ];
        }

        return $res;
    }

    /**
     * @return array<string>
     */
    public function getHierarchicalRoles(): array
    {
        if ($this->hierarchicalRoles === null || $this->hierarchicalRoles === []) {
            return [];
        }

        return $this->hierarchicalRoles;
    }

    /**
     * @param array<string>|null $hierarchicalRoles
     */
    public function setHierarchicalRoles(?array $hierarchicalRoles): self
    {
        $this->hierarchicalRoles = $hierarchicalRoles;

        return $this;
    }

    /**
     * @return Collection<int, RoleEntityPermission>
     */
    public function getDataPermissions(): Collection
    {
        return $this->dataPermissions;
    }

    public function addDataPermission(RoleEntityPermission $dataPermission): self
    {
        if (!$this->dataPermissions->contains($dataPermission)) {
            $this->dataPermissions->add($dataPermission);
            $dataPermission->setRole($this);
        }

        return $this;
    }

    public function removeDataPermission(RoleEntityPermission $dataPermission): self
    {
        // set the owning side to null (unless already changed)
        if ($this->dataPermissions->removeElement($dataPermission) && $dataPermission->getRole() === $this) {
            $dataPermission->setRole(null);
        }

        return $this;
    }


    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'valid' => $this->isValid(),
            'hierarchicalRoles' => $this->getHierarchicalRoles(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            ...$this->retrievePlainArray(),
            'permissions' => $this->getPermissions(),
            'userCount' => $this->getUsers()->count(),
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
