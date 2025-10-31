<?php

declare(strict_types=1);

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
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EnumExtra\Itemable;
use Tourze\LockServiceBundle\Model\LockEntity;

/**
 * @see https://symfony.com/doc/current/doctrine.html#creating-an-entity-class
 * @see https://symfony.com/doc/current/doctrine/reverse_engineering.html
 * @see https://docs.kilvn.com/skr-shop/src/account/#%E7%94%A8%E6%88%B7%E4%BD%93%E7%B3%BB
 * @implements AdminArrayInterface<string, mixed>
 * @implements PlainArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: BizUserRepository::class)]
#[ORM\Table(name: BizUser::TABLE_NAME, options: ['comment' => '系统用户'])]
class BizUser implements UserInterface, PasswordAuthenticatedUserInterface, Itemable, \Stringable, AdminArrayInterface, PlainArrayInterface, ApiArrayInterface, LockEntity
{
    use TimestampableAware;
    public const TABLE_NAME = 'biz_user';

    #[Groups(groups: ['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[Groups(groups: ['restful_read'])]
    #[TrackColumn]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '头像'])]
    private ?string $avatar = null;

    #[IndexColumn]
    #[Assert\Length(max: 60)]
    #[ORM\Column(type: Types::STRING, length: 60, nullable: true, options: ['comment' => '用户类型'])]
    private ?string $type = null;

    /**
     * @var string 一般可以用来表示 openid
     */
    #[Groups(groups: ['restful_read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => '用户名'])]
    private string $username;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '用户唯一标志'])]
    private ?string $identity = null;

    #[Groups(groups: ['restful_read'])]
    #[TrackColumn]
    #[Assert\Length(max: 191)]
    #[ORM\Column(type: Types::STRING, nullable: true, options: ['comment' => '昵称'])]
    private ?string $nickName = null;

    #[Assert\Email]
    #[Assert\Length(max: 191)]
    #[ORM\Column(type: Types::STRING, length: 191, nullable: true, options: ['comment' => '邮箱地址'])]
    private ?string $email = null;

    #[TrackColumn]
    #[Assert\Length(max: 190)]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号码格式不正确')]
    #[ORM\Column(length: 190, nullable: true, options: ['comment' => '手机号码'])]
    private ?string $mobile = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, nullable: true, options: ['comment' => '密码HASH'])]
    private ?string $passwordHash = null;

    /**
     * 临时存储明文密码，用于表单处理，不持久化到数据库
     */
    #[Assert\Length(max: 255)]
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, BizRole>
     */
    #[ORM\ManyToMany(targetEntity: BizRole::class, inversedBy: 'users', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'biz_user_biz_role')]
    private Collection $assignRoles;

    #[TrackColumn]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[Assert\Date]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '生日'])]
    private ?\DateTimeInterface $birthday = null;

    #[Assert\Length(max: 20)]
    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '性别'])]
    private ?string $gender = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '省份名称'])]
    private ?string $provinceName = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '城市名称'])]
    private ?string $cityName = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '区域名称'])]
    private ?string $areaName = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '详细地址'])]
    private ?string $address = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->assignRoles = new ArrayCollection();
    }

    public function __serialize(): array
    {
        // 清除敏感信息
        $this->plainPassword = null;

        // add $this->salt too if you don't use Bcrypt or Argon2i
        return ['id' => $this->id, 'username' => $this->username, 'passwordHash' => $this->passwordHash];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->passwordHash = $data['passwordHash'];
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
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

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function setNickName(?string $nickName): void
    {
        $this->nickName = $nickName;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function getUserIdentifier(): string
    {
        // 在创建新用户时，username 可能为空，返回默认值而不是抛出异常
        if ('' === $this->username) {
            return null !== $this->id ? (string) $this->id : '0';
        }

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

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRoles(): array
    {
        $roles = $this->collectUserRoles();
        $roles = $this->ensureDefaultRole($roles);

        return array_values(array_unique($roles));
    }

    /**
     * 收集用户角色
     *
     * @return string[]
     */
    private function collectUserRoles(): array
    {
        $roles = [];
        foreach ($this->getAssignRoles() as $assignRole) {
            $roles[] = $assignRole->getName();
            $roles = array_merge($roles, $this->processHierarchicalRoles($assignRole));
        }

        return $roles;
    }

    /**
     * 处理层次化角色
     *
     * @return string[]
     */
    private function processHierarchicalRoles(BizRole $assignRole): array
    {
        $roles = [];
        foreach ($assignRole->getHierarchicalRoles() as $hierarchicalRole) {
            $roles[] = $this->convertRoleToString($hierarchicalRole);
        }

        return $roles;
    }

    /**
     * 转换角色为字符串
     */
    private function convertRoleToString(mixed $role): string
    {
        if (is_array($role)) {
            return implode(',', $role);
        }

        if ($role instanceof BizRole) {
            return $role->getName();
        }

        return strval($role);
    }

    /**
     * 确保默认角色
     *
     * @param string[] $roles
     *
     * @return string[]
     */
    private function ensureDefaultRole(array $roles): array
    {
        if ([] === $roles) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
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

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIdentity(?string $identity): void
    {
        $this->identity = $identity;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>
     */
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
     * @return BizRole[]
     */
    public function getAssignRoles(): array
    {
        return $this->assignRoles
            ->filter(fn (BizRole $item): bool => true === $item->isValid())
            ->toArray()
        ;
    }

    public function addAssignRole(BizRole $assignRole): self
    {
        if (!$this->assignRoles->contains($assignRole)) {
            $this->assignRoles->add($assignRole);
        }

        return $this;
    }

    public function removeAssignRole(BizRole $assignRole): self
    {
        $this->assignRoles->removeElement($assignRole);

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function setProvinceName(string $provinceName): void
    {
        $this->provinceName = $provinceName;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getAreaName(): ?string
    {
        return $this->areaName;
    }

    public function setAreaName(?string $areaName): void
    {
        $this->areaName = $areaName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'avatar' => $this->getAvatar(),
            'username' => $this->getUsername(),
            'identity' => $this->getIdentity(),
            'nickName' => $this->getNickName(),
            'email' => $this->getEmail(),
            'mobile' => $this->getMobile(),
        ];
    }

    public function retrieveLockResource(): string
    {
        return 'biz_user_' . $this->getId();
    }
}
