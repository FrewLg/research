<?php

namespace App\Repository;

use App\Entity\InstitutionalReviewersBoard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InstitutionalReviewersBoard|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstitutionalReviewersBoard|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstitutionalReviewersBoard[]    findAll()
 * @method InstitutionalReviewersBoard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstitutionalReviewersBoardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstitutionalReviewersBoard::class);
    }

    // /**
    //  * @return InstitutionalReviewersBoard[] Returns an array of InstitutionalReviewersBoard objects
    //  */
     
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            
            ->setMaxResults(10)
            
            ->getQuery()
            ->getResult()
        ;
    }
   
    public function Irbmembersbycollege( $college )
{
    return $this->createQueryBuilder( 't' )
        ->select('t') // in this way
        ->leftJoin("App:User", "r", "with", "s.id=r.submission")

        ->orderBy( 't.id', 'ASC' )
        ->where( 't.college = :par1' )
        ->setParameter( 'par1', $college )
        ->getQuery()
        ->getResult()
        ;
}


    public function findByCollege($college)
        {

            return $this->createQueryBuilder('a')
                ->innerJoin('a.reviewer', 'd')
                ->innerJoin('d.userInfo', 'c') 
                ->andWhere('c.college = :e') 
                ->setParameter('e',$college)
                ->orderBy('a.id', 'ASC') 
                ->getQuery()
                ->getResult();

    //     $em = $this->getDoctrine()->getManager();
    //     $query = $em->createQuery(
    //         'SELECT   ui.first_name,  ui.last_name
    // FROM App:InstitutionalReviewersBoard s
    // JOIN s.reviewer u
    // JOIN u.userInfo ui
    //  WHERE  
    // ui.college = :college')
    //         ->setParameter('college', $college) ;
    //      $reviewers = $query->getResult();

    //         return   $reviewers 
    //         ;
        }   

    /*
    public function findOneBySomeField($value): ?InstitutionalReviewersBoard
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
