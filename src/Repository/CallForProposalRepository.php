<?php

namespace App\Repository;

use App\Entity\CallForProposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CallForProposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallForProposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallForProposal[]    findAll()
 * @method CallForProposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallForProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallForProposal::class);
    }

    // /**
    //  * @return CallForProposal[] Returns an array of CallForProposal objects
    //  */
     
    public function findByWorkunit($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?CallForProposal
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
