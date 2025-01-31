<?php

namespace App\Repository;

use App\Entity\Event;
use App\Repository\Traits\UuidFinderTrait;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUpcomingEvent(): ?Event
    {
        $qb = $this->findAllUpcomingEventsInOrderQueryBuilder();

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllUpcomingEventsInOrder()
    {
        $qb = $this->findAllUpcomingEventsInOrderQueryBuilder();
        dump(new DateTime());
        dd($qb->getQuery()->getResult());

        return $qb->getQuery()->getResult();
    }

    protected function findAllUpcomingEventsInOrderQueryBuilder(): QueryBuilder
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
        ;

        return $qb;
    }

    public function findLastPastEvent(): ?Event
    {
        return $this->createQueryBuilder('e')
            ->where('e.start < :today')
            ->setParameter('today', new DateTime('today'))
            ->orderBy('e.start', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
