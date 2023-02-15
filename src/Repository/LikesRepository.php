<?php

namespace App\Repository;

use App\Entity\Likes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Likes>
 *
 * @method Likes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Likes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Likes[]    findAll()
 * @method Likes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Likes::class);
    }

    public function save(Likes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Likes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Likes[] Returns an array of Likes objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Likes
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    //Creating function to find likes of users
    function findAllLikes($user)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM "publications" AS p WHERE p.id IN 
    (SELECT publication_id FROM likes l WHERE l.users_id = :user) 
ORDER BY p.id DESC';

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery(['user' => $user])->fetchAllAssociative();
    }

    //Function to count the likes of users
    function findCountLikes($publication_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT COUNT(*) as countLikes FROM "likes" INNER JOIN "publications" ON publications.id = :publication_id
        AND  likes.publication_id = :publication_id ';

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery(['publication_id' => $publication_id])->fetchAllAssociative();
    }
}
