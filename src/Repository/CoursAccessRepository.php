<?php

namespace App\Repository;

use App\Entity\CoursAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoursAccess>
 *
 * @method CoursAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursAccess[]    findAll()
 * @method CoursAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursAccess::class);
    }

//    /**
//     * @return CoursAccess[] Returns an array of CoursAccess objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CoursAccess
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
