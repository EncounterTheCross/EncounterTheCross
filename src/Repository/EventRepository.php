<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Repository\Traits\UuidFinderTrait;
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
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('e.start', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByServerCheckInToken(string $token): ?Event
    {
        $qb = $this->findByServerCheckInTokenQueryBuilder($token);

        $results = $qb->getQuery()
            ->getResult()
        ;

        if (0 === count($results) || count($results) > 1) {
            // TODO: add logging for more than one found
            return null;
        }

        return $results[0];
    }

    public function findByServerCheckInTokenQueryBuilder(string $token): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->select('e, l, lp, ep, person, eppt, lpc') // Explicitly select needed entities
//            ->select('partial e.{id, name, start, end}, partial lp.{id, name}, partial ep.{id, type}, partial person.{id, firstName, lastName}, l, eppt')
            ->leftJoin('e.location', 'l')  // leftJoin to main event location
            ->leftJoin('e.launchPoints', 'lp')  // leftJoin to launch points
            ->leftJoin('lp.eventAttendees', 'ep', 'WITH', 'ep.type = :type AND ep.status = :status AND ep.event = e')
            ->leftJoin('ep.person', 'person')
            ->leftJoin('ep.eventPrayerTeamServers', 'eppt')
            ->leftJoin('lp.launchPointContacts','lpc')
            ->where('e.checkInToken = :token')
            ->andWhere('e.active = :active')
            ->setParameter('token', $token)
            ->setParameter('type', EventParticipant::TYPE_SERVER)
            ->setParameter('status', \App\Enum\EventParticipantStatusEnum::ATTENDING->value)
            ->setParameter('active', true)
            ->orderBy('person.lastName', 'ASC')
            ->addOrderBy('person.firstName', 'ASC')
            ->addOrderBy('lp.name', 'ASC')
        ;
    }
}
