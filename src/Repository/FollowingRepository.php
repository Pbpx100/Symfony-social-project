<?php

namespace App\Repository;

use App\Entity\Following;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Following>
 *
 * @method Following|null find($id, $lockMode = null, $lockVersion = null)
 * @method Following|null findOneBy(array $criteria, array $orderBy = null)
 * @method Following[]    findAll()
 * @method Following[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Following::class);
    }

    public function save(Following $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Following $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Following[] Returns an array of Following objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Following
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    //Creating Function to find following users from database
    function findAllFollowings($user)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM "user" AS u WHERE u.id = :user OR 
    u.id IN (SELECT followed_id FROM following f WHERE f.users_id = :user) 
    ORDER BY u.id DESC';

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery(['user' => $user])->fetchAllAssociative();
    }

    //Creating Function to find followers users from database
    function findAllFollowers($user)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM "user" AS u WHERE u.id = :user OR 
    u.id IN (SELECT users_id FROM following f WHERE f.followed_id = :user) 
    ORDER BY u.id DESC';

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery(['user' => $user])->fetchAllAssociative();
    }
}
