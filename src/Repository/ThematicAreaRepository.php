<?php

namespace App\Repository;

use App\Entity\CallForProposal;
use App\Entity\College;
use App\Entity\ThematicArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->leftJoin("App:CallForProposal",  "t.id=call")
            ->setParameter('call', $call)
            ->setParameter('submission',  $call)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    ///
    public function getThematicAreaSubmissions(CallForProposal $callForProposal, College $college )
    {
        return $this->createQueryBuilder('t')

        ->andWhere("t.college = :college")
        ->leftJoin("App:CallForProposal",  "c.id=call")
        ->leftJoin("App:College",  "c.submission")
        ->setParameter('call', $callForProposal)
        ->setParameter('college', $college)
        ->getQuery()->getResult();
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
