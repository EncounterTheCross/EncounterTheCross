<?php

namespace App\Repository;

use App\Entity\Event;
use App\Repository\Traits\UuidFinderTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    use UuidFinderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Event|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUpcomingEvent()
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('e')
            ->andWhere(
                $qb->expr()->gte('e.start', ':today')
            )
            ->orWhere(
                $qb->expr()->gte('e.end', ':today')
            )
            ->setParameter('today', date('Y-m-d H:i:s', strtotime('tomorrow') - 1))
            ->orderBy('e.start', 'ASC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
