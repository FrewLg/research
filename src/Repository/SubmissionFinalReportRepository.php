<?php

namespace App\Repository;

use App\Entity\SubmissionFinalReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubmissionFinalReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubmissionFinalReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubmissionFinalReport[]    findAll()
 * @method SubmissionFinalReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubmissionFinalReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionFinalReport::class);
    }

    // /**
    //  * @return SubmissionFinalReport[] Returns an array of SubmissionFinalReport objects
    //  */
  
    public function hasSubmissionFinalReport($submission)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.submission = :submission')
            ->setParameter('submission', $submission)
            ->orderBy('s.id', 'ASC')
           
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
   

    /*
    public function findOneBySomeField($value): ?SubmissionFinalReport
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
