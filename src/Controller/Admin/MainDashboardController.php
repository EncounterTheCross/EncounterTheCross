<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\EventLocationCrudController;
use App\Controller\Admin\Crud\LaunchPointCrudController;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\Leader;
use App\Entity\Location;
use App\Entity\Testimonial;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainDashboardController extends AbstractDashboardController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/page/content.html.twig');

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Leaders Admin');
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

        yield MenuItem::section('Launch Points');
        yield MenuItem::linkToCrud(
            'Launch Points',
            'fas fa-list',
            Location::class)
            ->setController(LaunchPointCrudController::class)
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;

        yield MenuItem::section('Testimonies');
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
}
