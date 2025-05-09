<?php

namespace BizUserBundle\Repository;

use BizUserBundle\Entity\RoleEntityPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoleEntityPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleEntityPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleEntityPermission[]    findAll()
 * @method RoleEntityPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleEntityPermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleEntityPermission::class);
    }
}
