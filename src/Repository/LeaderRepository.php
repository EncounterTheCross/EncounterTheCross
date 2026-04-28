<?php

namespace App\Repository;

use App\Entity\Leader;
use App\Service\RoleManager\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<Leader>
 *
 * @method Leader|null find($id, $lockMode = null, $lockVersion = null)
 * @method Leader|null findOneBy(array $criteria, array $orderBy = null)
 * @method Leader[]    findAll()
 * @method Leader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaderRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leader::class);
    }

    public function save(Leader $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Leader $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Leader) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findAllLeadersWithNotificationOnAndActive()
    {
        $qb = $this->createQueryBuilder('l');
        $qb
            ->leftJoin('l.person', 'p')
            // ->select('l.email as Email', "CONCAT(p.firstName,' ',p.lastName) as Name")
        ;

        $leaders = $qb->getQuery()->getResult();
        $leaders = new ArrayCollection($leaders);
        $toEmails = $leaders
            ->filter(fn(Leader $leader) => $leader->isLeader())
            ->map(fn(Leader $leader) => [$leader->getEmail(), $leader->getPerson()->getFullName()])->toArray();

        return $toEmails;
    }

    public function findEventLeaders()
    {
        $rsm = $this->createResultSetMappingBuilder('l');

        $rawQuery = sprintf(
            'SELECT %s
        FROM leader l
        WHERE JSON_SEARCH(l.roles, \'one\', :role) is not null',
            $rsm->generateSelectClause()
        );
        $query = $this->getEntityManager()->createNativeQuery($rawQuery, $rsm);
        $query->setParameter('role', Role::LEADER_EVENT);

        return $query->getResult();
        //        $qb = $this->createQueryBuilder('l');
        //
        //        $qb
        //            ->select('l')
        // //            ->andWhere(
        // //                "JSON_EXTRACT(l.roles, JSON_SEARCH(l.roles, 'one', ':role'))"
        // //            )
        // //            ->setParameter('role',Role::LEADER_EVENT)
        //            ->setMaxResults(3);
        //
        //        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Leader[] Returns an array of Leader objects
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

    //    public function findOneBySomeField($value): ?Leader
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
