<?php

namespace App\Repository;

use App\Entity\StepsRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StepsRequest>
 *
 * @method StepsRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method StepsRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method StepsRequest[]    findAll()
 * @method StepsRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StepsRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StepsRequest::class);
    }

    public function save(StepsRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StepsRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Return number StepsRequest
     */
    public function countByDate() {

        $query = $this->createQueryBuilder('s')
                ->select('SUBSTRING(s.createdAt, 1, 10) as datesteps, COUNT(s) as count')
                ->groupBy('datesteps');

        return $query->getQuery()->getResult();
            
    } 


//    /**
//     * @return StepsRequest[] Returns an array of StepsRequest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StepsRequest
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
