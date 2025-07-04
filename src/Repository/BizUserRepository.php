<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\BizUser;
use BizUserBundle\Exception\UsernameInvalidException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * @method BizUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method BizUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method BizUser[]    findAll()
 * @method BizUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[Autoconfigure(public: true)]
#[AsAlias(id: UserLoaderInterface::class)]
#[AsAlias(id: UserManagerInterface::class)]
class BizUserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface, UserManagerInterface
{
    public function __construct(ManagerRegistry $registry)
    {
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
                'id' => $identifier,
                'valid' => true,
            ]);
        }
        if ($user === null) {
            $user = $this->findOneBy([
                'username' => $identifier,
                'valid' => true,
            ]);
        }

        return $user;
    }

    /**
     * 需要保留的系统用户名关键词
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

    public function em(): EntityManagerInterface
    {
        return $this->getEntityManager();
    }

    public function createUser(string $userIdentifier, ?string $nickName = null, ?string $avatarUrl = null): UserInterface
    {
        $user = new BizUser();
        $user->setUsername($userIdentifier);
        if ($nickName !== null) {
            $user->setNickName($nickName);
        }
        if ($avatarUrl !== null) {
            $user->setAvatar($avatarUrl);
        }
        return $user;
    }
}
