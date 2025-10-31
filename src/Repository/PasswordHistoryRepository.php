<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\PasswordHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<PasswordHistory>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: PasswordHistory::class)]
class PasswordHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordHistory::class);
    }

    public function findLatestPasswordHistory(string $username): ?PasswordHistory
    {
        return $this->findOneBy(['username' => $username], ['createTime' => 'DESC']);
    }

    /**
     * 保存密码历史实体到数据库
     */
    public function save(PasswordHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 从数据库中移除密码历史实体
     */
    public function remove(PasswordHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
