<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\PasswordHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @method PasswordHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordHistory[]    findAll()
 * @method PasswordHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[Autoconfigure(public: true)]
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
}
