<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\BizRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BizRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method BizRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method BizRole[]    findAll()
 * @method BizRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BizRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BizRole::class);
    }
}
