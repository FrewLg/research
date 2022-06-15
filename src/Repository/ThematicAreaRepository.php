<?php

namespace App\Repository;

use App\Entity\CallForProposal;
use App\Entity\College;
use App\Entity\ThematicArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThematicArea|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThematicArea|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThematicArea[]    findAll()
 * @method ThematicArea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThematicAreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThematicArea::class);
    }

// /**
//  * @return ThematicArea[] Returns an array of ThematicArea objects
//  */

    public function findByExampleField($call, $college)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.college = :college')
            ->setParameter('call', $call)
            ->setParameter('college',  $college)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    /////////New rep
    public function findByCall($call)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.call = :call')
            ->leftJoin("App:Submission",  "s")
            ->andWhere('s.call =: call  ')

            ->setParameter('call', $call)
            ->setParameter('submission',  $call)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    ///
  
    public function submissionByCall($value)
    {
       return $this->createQueryBuilder('a')
                ->innerJoin('a.submissions', 's')
                ->innerJoin('s.callForProposal', 'd')
                // ->innerJoin('d.thematicArea', 't')
                 ->andWhere('d.id = :e') 
                //  ->andWhere('t.id = :ta') 
                ->setParameter('e', $value)
                // ->setParameter('ta', [$value->getThematicArea() ])
                // ->orderBy('a.id', 'ASC') 
                ->getQuery()
                ->getResult()
            ;
        }

        public function dsubmissionByThemeCall($value) 
        {
            $qb= $this->createQueryBuilder('c');
            // ->select("count(s.id)");
            $userpublication = $qb
            // ->select('t.name as name , s.title as title ,  u.username')
             ->innerJoin('c.submissions', 's')
            ->innerJoin('s.callForProposal','sc') 
             ->andWhere('sc.id  =:id') 
             ->setParameter('id', $value)
              ->getQuery()->getResult();
    
            return   $userpublication 
            ;
        } 
    
            public function submissionByThemeCall( $value) 
        {
            //  $entityManager = $this->getDoctrine()->getManager();
            // $qb= $this->createQueryBuilder('c');
            $query = $this->getEntityManager()
            ->createQuery(
              'SELECT t.name, a.first_name, c.id, s.title 
               FROM App:ThematicArea t
               JOIN t.submissions s
               JOIN s.callForProposal c
               JOIN s.author u
               JOIN u.userInfo a
               WHERE c.id =:val')
            ->setParameter('val', $value);
            // ->setParameter('cstatus', 'completed');
            $recepients = $query->getScalarResult();
            return   $recepients;
        }  


    /*
    public function findOneBySomeField($value): ?ThematicArea
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
