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
    

    public function getSubmissions($status=null)
    {
        $qb= $this->createQueryBuilder('s');
        if(isset($status)){
            
            $qb->leftJoin("App:Review","r","with","s.id=r.submission");
            $qb->andWhere("r.remark= :remark")
            ->setParameter("remark",$status);
            
        }
        $qb->groupBy("s.id")->andHaving("count(s)>1");
 
// dd($qb->orderBy('s.id', 'ASC')->getQuery()->getSQL());
          return  $qb->orderBy('s.id', 'ASC')
            ->getQuery();

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
    #################
    // public function findBySubmissionByDepartment($value): ?Submission
    // {
    //    return $this->createQueryBuilder('a')
    //             ->innerJoin('a.department', 'd')
    //             ->innerJoin('d.college', 'c') 
    //             ->andWhere('c.id = :e') 
    //             ->setParameter('e', $value)
    //             ->orderBy('a.id', 'ASC') 
    //             ->getQuery()
    //             ->getResult()
    //         ;
    //     }
        
// public function findBySStatus(): ?Submission
//     {
//     $em = $this->getDoctrine()->getManager();
//     $query = $em->createQuery(
//         'SELECT u.email , p.id,    u.username,  p.complete, p.title  , ui.first_name
// FROM App:CoAuthor s
// JOIN s.researcher u
// JOIN u.userInfo ui
// JOIN s.submission p
// WHERE  
// p.complete is NULL');
//         // ->setParameter('submission', $submission) 
//         // ->setParameter('cstatus', 'completed' );
//     $recepients = $query->getResult();
 
//         return   $recepients 
//         ;
//     }   
}
