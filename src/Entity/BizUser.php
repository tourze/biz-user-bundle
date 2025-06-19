<?php

namespace BizUserBundle\Entity;

use BizUserBundle\Repository\BizUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EnumExtra\Itemable;
use Tourze\LockServiceBundle\Model\LockEntity;

/**
 * @see https://symfony.com/doc/current/doctrine.html#creating-an-entity-class
 * @see https://symfony.com/doc/current/doctrine/reverse_engineering.html
 * @see https://docs.kilvn.com/skr-shop/src/account/#%E7%94%A8%E6%88%B7%E4%BD%93%E7%B3%BB
 */
#[ORM\Entity(repositoryClass: BizUserRepository::class)]
#[ORM\Table(name: BizUser::TABLE_NAME, options: ['comment' => '系统用户'])]
class BizUser implements UserInterface, PasswordAuthenticatedUserInterface, Itemable, \Stringable, AdminArrayInterface, PlainArrayInterface, ApiArrayInterface, LockEntity
{
    use TimestampableAware;
    public const TABLE_NAME = 'biz_user';

    #[Groups(['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[Groups(['restful_read'])]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '头像'])]
    private ?string $avatar = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 60, nullable: true, options: ['comment' => '用户类型'])]
    private ?string $type = null;

    /**
     * @var string 一般可以用来表示 openid
     */
    #[Groups(['restful_read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => '用户名'])]
    private string $username;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '用户唯一标志'])]
    private ?string $identity = null;

    #[Groups(['restful_read'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, nullable: true, options: ['comment' => '昵称'])]
    private ?string $nickName = '';

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '邮箱地址'])]
    private ?string $email = null;

    #[TrackColumn]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '手机号码'])]
    private ?string $mobile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['comment' => '密码HASH'])]
    private ?string $passwordHash = null;

    /**
     * 临时存储明文密码，用于表单处理，不持久化到数据库
     */
    private ?string $plainPassword = null;

    /**
     * @var Collection<BizRole>
     */
    #[ORM\ManyToMany(targetEntity: BizRole::class, inversedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $assignRoles;

    /**
     * @var Collection<UserAttribute>
     */
    #[Groups(['restful_read'])]
    #[ORM\OneToMany(targetEntity: UserAttribute::class, mappedBy: 'user', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true, indexBy: 'name')]
    private Collection $attributes;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '生日'])]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '性别'])]
    private ?string $gender = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '省份名称'])]
    private ?string $provinceName = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '城市名称'])]
    private ?string $cityName = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '区域名称'])]
    private ?string $areaName = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '详细地址'])]
    private ?string $address = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->assignRoles = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    public function __serialize(): array
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return [$this->id, $this->username, $this->passwordHash];
    }

    public function __unserialize(array $data): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->username, $this->passwordHash] = $data;
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '(未保存用户)';
        }

        return strval($this->getNickName());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function setNickName(string $nickName): void
    {
        $this->nickName = $nickName;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->getPasswordHash();
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->getAssignRoles() as $assignRole) {
            $roles[] = $assignRole->getName();
            foreach ($assignRole->getHierarchicalRoles() as $hierarchicalRole) {
                if (is_array($hierarchicalRole)) {
                    $roles[] = implode(',', $hierarchicalRole);
                } elseif ($hierarchicalRole instanceof BizRole) {
                    $roles[] = $hierarchicalRole->getName();
                } else {
                    $roles[] = strval($hierarchicalRole);
                }
            }
        }

        // guarantees that a user always has at least one role for security
        if ([] === $roles) {
            $roles[] = 'ROLE_USER';
        }

        return array_values(array_unique($roles));
    }

    public function getSalt(): ?string
    {
        // We're using bcrypt in security.yaml to encode the password, so
        // the salt value is built-in and you don't have to generate one
        // See https://en.wikipedia.org/wiki/Bcrypt
        return null;
    }

    public function eraseCredentials(): void
    {
        // 清除明文密码
        $this->plainPassword = null;
    }

    /**
     * @internal 尽量改用 avatarService 来读取头像
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIdentity(?string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function toSelectItem(): array
    {
        return [
            'label' => $this->getNickName(),
            'text' => $this->getNickName(),
            'value' => $this->getId(),
            'name' => $this->getNickName(),
        ];
    }

    /**
     * 获取当前有效的角色列表
     *
     * @return array|BizRole[]
     */
    public function getAssignRoles(): array
    {
        return $this->assignRoles
            ->filter(fn (BizRole $item) => $item->isValid())
            ->toArray();
    }

    public function addAssignRole(BizRole $assignRole): self
    {
        if (!$this->assignRoles->contains($assignRole)) {
            $this->assignRoles[] = $assignRole;
        }

        return $this;
    }

    public function removeAssignRole(BizRole $assignRole): self
    {
        $this->assignRoles->removeElement($assignRole);

        return $this;
    }

    /**
     * @return Collection<int, UserAttribute>
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(UserAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setUser($this);
        }

        return $this;
    }

    public function removeAttribute(UserAttribute $attribute): self
    {
        // set the owning side to null (unless already changed)
        if ($this->attributes->removeElement($attribute) && $attribute->getUser() === $this) {
            $attribute->setUser(null);
        }

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

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function setProvinceName(string $provinceName): self
    {
        $this->provinceName = $provinceName;

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getAreaName(): ?string
    {
        return $this->areaName;
    }

    public function setAreaName(?string $areaName): self
    {
        $this->areaName = $areaName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function retrieveAdminArray(): array
    {
        $result = [
            ...$this->retrievePlainArray(),
            'roles' => [],
        ];

        foreach ($this->getAssignRoles() as $assignRole) {
            $result['roles'][] = $assignRole->retrievePlainArray();
        }

        return $result;
    }

    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'identity' => $this->getIdentity(),
            'nickName' => $this->getNickName(),
            'email' => $this->getEmail(),
            'mobile' => $this->getMobile(),
            'valid' => $this->isValid(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    public function retrieveApiArray(): array
    {
        $result = [
            'id' => $this->getId(),
            'avatar' => $this->getAvatar(),
            'username' => $this->getUsername(),
            'identity' => $this->getIdentity(),
            'nickName' => $this->getNickName(),
            'email' => $this->getEmail(),
            'mobile' => $this->getMobile(),
            'attributes' => [],
        ];

        foreach ($this->getAttributes() as $attribute) {
            $result['attributes'][] = $attribute->retrieveApiArray();
        }

        return $result;
    }

    public function retrieveLockResource(): string
    {
        return 'biz_user_' . $this->getId();
    }
}
