<?php

namespace App\Repository\VenueBooking;

use App\Entity\VenueBooking\RoomConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomConfiguration>
 */
class RoomConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomConfiguration::class);
    }

    public function save(RoomConfiguration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RoomConfiguration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find the default configuration for a room
     */
    public function findDefaultForRoom(int $roomId): ?RoomConfiguration
    {
        return $this->createQueryBuilder('rc')
            ->andWhere('rc.room = :roomId')
            ->andWhere('rc.isDefault = :isDefault')
            ->setParameter('roomId', $roomId)
            ->setParameter('isDefault', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all active configurations for a room
     */
    public function findActiveConfigurationsForRoom(int $roomId): array
    {
        return $this->createQueryBuilder('rc')
            ->andWhere('rc.room = :roomId')
            ->andWhere('rc.isActive = :isActive')
            ->setParameter('roomId', $roomId)
            ->setParameter('isActive', true)
            ->orderBy('rc.capacity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active configurations for a room valid for a specific date
     */
    public function findConfigurationsForRoomAndDate(int $roomId, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('rc')
            ->andWhere('rc.room = :roomId')
            ->andWhere('rc.isActive = :isActive')
            ->andWhere('(rc.validFromMonth IS NULL OR rc.validFromMonth <= :date)')
            ->andWhere('(rc.validUntilMonth IS NULL OR rc.validUntilMonth >= :date)')
            ->setParameter('roomId', $roomId)
            ->setParameter('isActive', true)
            ->setParameter('date', intval($date->format('n')))
            ->orderBy('rc.capacity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the default configuration for a room valid for a specific date
     */
    public function findDefaultConfigurationForDate(int $roomId, \DateTimeInterface $date): ?RoomConfiguration
    {
        return $this->createQueryBuilder('rc')
            ->andWhere('rc.room = :roomId')
            ->andWhere('rc.isActive = :isActive')
            ->andWhere('rc.isDefault = :isDefault')
            ->andWhere('(rc.validFromMonth IS NULL OR rc.validFromMonth <= :date)')
            ->andWhere('(rc.validUntilMonth IS NULL OR rc.validUntilMonth >= :date)')
            ->setParameter('roomId', $roomId)
            ->setParameter('isActive', true)
            ->setParameter('isDefault', true)
            ->setParameter('date', intval($date->format('n')))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
