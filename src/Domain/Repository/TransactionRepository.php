<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTransactionHistoryByUser(int $userId): array
    {
        $query = $this->createQueryBuilder('t');

        return $query->select(
                't.amount as value',
                    'upayer.name as payer',
                    'upayee.name as payee',
                    't.created_at'
            )
            ->leftJoin(User::class, 'upayee', 'WITH', 't.payee_id = upayee.id')
            ->leftJoin(User::class, 'upayer', 'WITH', 't.payer_id = upayer.id')
            ->where('t.payee_id = :user')
            ->orWhere('t.payer_id = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getArrayResult();
    }
}