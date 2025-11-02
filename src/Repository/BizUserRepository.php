<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Exception\UsernameInvalidException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BizRoleBundle\Repository\BizRoleRepository;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * 业务用户仓储类，负责用户数据的查询操作
 * 同时实现了用户加载接口和密码升级接口
 *
 * @extends ServiceEntityRepository<BizUser>
 */
#[Autoconfigure(public: true)]
#[AsAlias(id: UserLoaderInterface::class)]
#[AsAlias(id: UserManagerInterface::class)]
#[AsRepository(entityClass: BizUser::class)]
class BizUserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface, UserManagerInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly BizRoleRepository $roleRepository,
    ) {
        parent::__construct($registry, BizUser::class);
    }

    /**
     * 用于自动密码升级
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof BizUser) {
            throw new UnsupportedUserException(sprintf('用户类 %s 不支持。', get_class($user)));
        }

        $user->setPasswordHash($newHashedPassword);
        $this->getEntityManager()->flush();
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $user = null;
        if (is_numeric($identifier)) {
            $user = $this->findOneBy([
                'id' => (int) $identifier,
                'valid' => true,
            ]);
        }
        if (null === $user) {
            $user = $this->findOneBy([
                'username' => $identifier,
                'valid' => true,
            ]);
        }

        return $user;
    }

    /**
     * 需要保留的系统用户名关键词
     *
     * @return string[]
     */
    public function getReservedUserNames(): array
    {
        return [
            'admin',
            'administrator',
            'system',
            'root',
            '管理员',
            '系统管理员',
        ];
    }

    /**
     * 检查用户名是否合法
     *
     * @throws UsernameInvalidException
     */
    public function checkUserLegal(BizUser $user): void
    {
        foreach ($this->getReservedUserNames() as $reservedUserName) {
            if ($user->getUsername() === $reservedUserName) {
                throw new UsernameInvalidException('用户名不合法');
            }
        }
    }

    /**
     * 保存业务用户实体到数据库
     */
    public function save(BizUser $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 从数据库中移除业务用户实体
     */
    public function remove(BizUser $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 搜索用户（用于自动完成等场景）
     *
     * @return array<array{id: mixed, text: string}>
     */
    public function searchUsers(string $query, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'u.username', 'u.nickName')
            ->where('u.valid = :valid')
            ->andWhere('u.username LIKE :query OR u.nickName LIKE :query')
            ->setParameter('valid', true)
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
        ;

        /** @var list<array{id: mixed, username?: string, nickName?: string}> $results */
        $results = $qb->getQuery()->getArrayResult();

        $items = [];
        foreach ($results as $user) {
            $nickName = $user['nickName'] ?? null;
            $username = $user['username'] ?? '';
            $items[] = [
                'id' => $user['id'],
                'text' => (string) ($nickName ?? $username),
            ];
        }
        return $items;
    }

    /**
     * @param array<string|int, mixed> $roles
     */
    public function createUser(string $userIdentifier, ?string $nickName = null, ?string $avatarUrl = null, ?string $password = null, array $roles = []): UserInterface
    {
        $user = new BizUser();
        $user->setUsername($userIdentifier);
        if (null !== $nickName) {
            $user->setNickName($nickName);
        }
        if (null !== $avatarUrl) {
            $user->setAvatar($avatarUrl);
        }
        if (null !== $password) {
            $user->setPasswordHash($this->passwordHasher->hashPassword($user, $password));
        }
        foreach ($roles as $role) {
            if (is_string($role)) {
                // @phpstan-ignore-next-line tourze.crossModule
                $user->addAssignRole($this->roleRepository->findOrCreate($role));
            }
        }
        // 如果用户名是邮箱格式，也设置邮箱
        if (false !== filter_var($userIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user->setEmail($userIdentifier);
        }
        $user->setValid(true);

        return $user;
    }

    public function saveUser(UserInterface $user): void
    {
        if (!$user instanceof BizUser) {
            throw new UnsupportedUserException(sprintf('用户类 %s 不支持。', get_class($user)));
        }

        $this->save($user, true);
    }
}
