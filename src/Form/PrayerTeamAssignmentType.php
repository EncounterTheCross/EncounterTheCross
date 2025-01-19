<?php

namespace App\Form;

use App\Entity\EventPrayerTeamServer;
use App\Entity\Leader;
use App\Entity\PrayerTeam;
use App\Repository\LeaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrayerTeamAssignmentType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager, private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            // Get the EventParticipant from the parent forms
            $eventParticipant = $data?->getEventParticipant();
            $email = $eventParticipant?->getPerson()?->getEmail();

            // Get filtered prayer teams based on email
            $prayerTeams = $this->getFilteredPrayerTeams($email);

            $form->add('PrayerTeam', EntityType::class, [
                'class' => PrayerTeam::class,
                'choices' => $prayerTeams,
                'placeholder' => 'Choose a Prayer Team',
                'choice_label' => 'name', // Adjust this to match your PrayerTeam entity property
            ]);
        });
    }

    private function getFilteredPrayerTeams(?string $email): array
    {
        if (!$email) {
            return [];
        }

        /** @var LeaderRepository $leaderRepo */
        $leaderRepo = $this->entityManager->getRepository(Leader::class);

        $queryBuilder = $this->entityManager->getRepository(PrayerTeam::class)
            ->createQueryBuilder('pt');

        // Example: Is a leader account show all prayer teams
        $leaders = $leaderRepo->findBy(['email' => $email]);

        if (str_ends_with($email, '@encounterthecross.com') || !empty($leaders) || $this->security->isGranted('ROLE_DATA_EDITOR_OVERWRITE')) {
            return $queryBuilder->getQuery()->getResult();
        }

        // Example: For specific email, exclude restricted prayer teams
        return $queryBuilder
            ->where('pt.requiresIntersession = :restricted')
            ->setParameter('restricted', false)
            ->getQuery()
            ->getResult();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventPrayerTeamServer::class,
        ]);
    }
}
