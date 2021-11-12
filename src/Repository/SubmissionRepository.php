<?php

namespace App\Repository;

use App\Entity\Submission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Submission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Submission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Submission[]    findAll()
 * @method Submission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }

    // /**
    //  * @return Submission[] Returns an array of Submission objects
    //  */
  
    public function getCount($filter=[])
    {
        $qb= $this->createQueryBuilder('s')->select("count(s.id)");
        if(isset($filter["submisstion_type"] ) && sizeof($filter["submisstion_type"] )>0 )
           $qb ->andWhere('s.submission_type in (:submission_type)')
            ->setParameter('submission_type', $filter["submisstion_type"]);
        if(isset($filter["author"] ) )
           $qb ->andWhere('s.author = :author')
            ->setParameter('author', $filter["author"]);

          return  $qb->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
    

    
    // public function findBySubmissionByUser($value): ?Submission
    // {
    //     $qb= $this->createQueryBuilder('s');
    //     // ->select("count(s.id)");
    //     $userpublication = $qb
    //     ->select('COUNT(e.id) as Proposals , e.submission_type as Subbmission_type')
    //     ->from('App\Entity\Submission', 'e')
    //     ->andWhere('e.author = :publisher')
    //     ->setParameter('publisher', $value)
    //     ->groupBy('e.submission_type')
    //     ->getQuery()->getResult();

    //     return   $userpublication 
    //     ;
    // }
    
}
