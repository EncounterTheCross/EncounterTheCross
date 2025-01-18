<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\EventLocationCrudController;
use App\Controller\Admin\Crud\LaunchPointCrudController;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Entity\Leader;
use App\Entity\Location;
use App\Entity\PrayerTeam;
use App\Entity\Testimonial;
use App\Enum\EventParticipantStatusEnum;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class MainDashboardController extends AbstractDashboardController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $upcomingEvent = $this->eventRepository->findUpcomingEvent();
        $launchPointData = new ArrayCollection();

        $participants = $upcomingEvent->getEventParticipants(EventParticipantStatusEnum::ATTENDING);

        foreach ($participants as $participant) {
            $launchName = $participant->getLaunchPoint()->getName();
            if (null === $launchPointData->get($launchName)) {
                $launchPointCounter = new ArrayCollection();
                $launchPointCounter->set('servers', new ArrayCollection());
                $launchPointCounter->set('attendees', new ArrayCollection());

                $launchPointData->set($launchName, $launchPointCounter);
            }

            if ($participant->isServer()) {
                $launchPointData->get($launchName)->get('servers')->add($participant);
                continue;
            }

            $launchPointData->get($launchName)->get('attendees')->add($participant);
        }

        // Prepare data for outer ring (per location)
        $locationAttendees = array_values($launchPointData->map(function (ArrayCollection $point) {
            return $point->get('attendees')->count();
        })->toArray());

        $locationServers = array_values($launchPointData->map(function (ArrayCollection $point) {
            return $point->get('servers')->count();
        })->toArray());

        $locationLabels = $launchPointData->getKeys();

        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);
        $attendeesColors = [
            'rgba(0, 136, 254, 0.8)',
            'rgba(75, 167, 255, 0.8)',
            'rgba(127, 193, 255, 0.8)',
            'rgba(168, 212, 255, 0.8)',
            'rgba(209, 232, 255, 0.8)',
        ];

        $serversColors = [
            'rgba(0, 196, 159, 0.8)',
            'rgba(64, 211, 180, 0.8)',
            'rgba(112, 225, 201, 0.8)',
            'rgba(159, 237, 221, 0.8)',
            'rgba(207, 250, 242, 0.8)',
        ];

        $multilevel = [
            [
                'data' => [
                    $upcomingEvent->getTotalAttendees(),
                    $upcomingEvent->getTotalServers(),
                ],
                'backgroundColor' => [$attendeesColors[0], $serversColors[0]],
                'labels' => ['Total Attendees', 'Total Servers'],
                'borderWidth' => 1,
                'weight' => 0.4,
            ],
            [
                'data' => $locationAttendees,
                'backgroundColor' => $attendeesColors,
                'labels' => $locationLabels,
                'borderWidth' => 1,
                'weight' => 0.3,
            ],
            [
                'data' => $locationServers,
                'backgroundColor' => $serversColors,
                'labels' => $locationLabels,
                'borderWidth' => 1,
                'weight' => 0.3,
            ],
        ];

        $chart->setData([
            //            'labels' => ['Servers', 'Attendees'],
            'datasets' => $multilevel,
        ]);
        $chart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ]);

        return $this->render('admin/page/mainDashboard.html.twig', [
            'event' => $upcomingEvent,
            'chart' => $chart,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Leaders Admin')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Site Settings', 'fas fa-cogs', '/admin/settings/edit')
            ->setPermission('ROLE_SUPER_ADMIN')
        ;
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Encounter Events');
        yield MenuItem::linkToCrud('Events', 'fas fa-list', Event::class);
        // yield MenuItem::linkToCrud('Events Participants', 'fas fa-list', EventParticipant::class);
        yield MenuItem::linkToCrud(
            'Event Locations',
            'fas fa-list',
            Location::class)
            ->setController(EventLocationCrudController::class)
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;

        yield MenuItem::section('Prayer Teams')
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;
        yield MenuItem::linkToCrud('Prayer Teams', 'fas fa-list', PrayerTeam::class)
            ->setPermission('ROLE_SUPER_ADMIN')
        ;
        yield MenuItem::linkToRoute('Event PT Assignments', null, 'event_prayer_team_assignments')
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;
        //        yield MenuItem::linkToCrud('PT Assignment Report',null, EventPrayerTeamServer::class);

        yield MenuItem::section('Launch Points')
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;
        yield MenuItem::linkToCrud(
            'Launch Points',
            'fas fa-list',
            Location::class)
            ->setController(LaunchPointCrudController::class)
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;

        yield MenuItem::section('Testimonies')
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;
        yield MenuItem::linkToCrud('Testimonial', 'fas fa-list', Testimonial::class)
            ->setPermission('ROLE_TESTIMONIAL_REVIEWER');

        yield MenuItem::section('Leadership');
        yield MenuItem::linkToCrud('Leaders', 'fas fa-list', Leader::class);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets()
            ->addAssetMapperEntry(Asset::new('ea_dashboard')->onlyOnIndex());

        //        dd($assets);

        return $assets;
    }
}
