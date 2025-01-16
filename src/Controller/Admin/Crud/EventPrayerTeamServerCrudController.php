<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Event;
use App\Entity\EventPrayerTeamServer;
use App\Entity\PrayerTeam;
use App\Repository\PrayerTeamRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\HttpFoundation\RequestStack;

class EventPrayerTeamServerCrudController extends AbstractCrudController
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return EventPrayerTeamServer::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);

        /** @var PrayerTeamRepository $prayerTeamRepository */
        $prayerTeamRepository = $this->container->get('doctrine')->getRepository(PrayerTeam::class);
        $leadershipPrayerTeams = [];
        foreach ($prayerTeamRepository->findBy(['requiresIntersession' => true]) as $prayerTeam) {
            $leadershipPrayerTeams[$prayerTeam->getId()] = $prayerTeam->getName();
        }
        if (!empty($leadershipPrayerTeams)) {
            $teamsFilter = ChoiceFilter::new('PrayerTeam')
                ->setChoices(array_flip($leadershipPrayerTeams))
            ;
            $filters->add($teamsFilter);
        }

        $eventId = $this->requestStack->getCurrentRequest()->query->get('event');
        if (!empty($eventId)) {
            $event = $this->container->get('doctrine')->getRepository(Event::class)
                ->findOneById($eventId);

            $eventFilter = ChoiceFilter::new('event')
                ->setChoices([$event->getName() => $event->getId()])
            ;

            $filters->add($eventFilter);
        }
        // dd($this->requestStack->getCurrentRequest()->query->all());

        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('eventParticipant')->hideOnForm(),
            TextField::new('PrayerTeam')->hideOnForm(),
            AssociationField::new('PrayerTeam')->onlyOnForms(),
            AssociationField::new('intersessionAssignment')
                ->renderAsNativeWidget(),
        ];
    }
}
