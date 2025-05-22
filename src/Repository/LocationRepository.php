<?php

namespace App\Repository;

use App\Entity\Location;
use App\Repository\Traits\UuidFinderTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 *
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends ServiceEntityRepository
{
    use UuidFinderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function save(Location $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Location $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public static function queryBuilderFilterByLocationType(string $type, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();
        $queryBuilder->andWhere(
            $expr->eq(
                'entity.type',
                ':type'
            )
        )
            ->setParameter(
                'type',
                $type
            );

        return $queryBuilder;
    }

    public function getAllActiveLaunchPoints(array $sort = []): array
    {
        $qb = $this->findByActiveLoctionsQueryBuilderByType(Location::TYPE_LAUNCH_POINT);

        $alias = $qb->getRootAliases();

        foreach ($sort as $by => $direction) {
            $qb = $qb->addOrderBy($alias[0].'.'.$by, $direction);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function getAllActiveEventLocations(): array
    {
        return $this->findByActiveLoctionsQueryBuilderByType(Location::TYPE_EVENT)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByActiveLoctionsQueryBuilderByType(string $type): QueryBuilder
    {
        $qb = $this->createQueryBuilder('lp');
        $qb
            ->andWhere(
                $qb->expr()->eq('lp.type', ':type')
            )
            ->andWhere(
                $qb->expr()->eq(
                    'lp.active',
                    ':active'
                )
            )
            ->setParameter('active', true)
            ->setParameter('type', $type)
        ;

        return $qb;
    }

    //    /**
    //     * @return Location[] Returns an array of Location objects
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

    //    public function findOneBySomeField($value): ?Location
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
