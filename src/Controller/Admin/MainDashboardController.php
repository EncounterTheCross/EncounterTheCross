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
use App\Repository\EventRepository;
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

        //        dd($this->eventRepository->findLastPastEvent());

        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);

        $chart->setData([
            'labels' => ['Servers', 'Attendees'],
            'datasets' => [
                [
                    'data' => [
                        $upcomingEvent->getTotalServers(),
                        $upcomingEvent->getTotalAttendees(),
                    ],
                    'backgroundColor' => ['#007bff', '#dc3545'],
                ],
            ],
        ]);
        $chart->setOptions([]);

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
