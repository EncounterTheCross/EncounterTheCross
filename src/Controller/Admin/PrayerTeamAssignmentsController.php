<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Event;
use App\Entity\Location;
use App\Form\EventServersType;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/encounter/{event}/prayer_team_assignments',
)]
class PrayerTeamAssignmentsController extends AbstractController
{
    #[Route(
        path: '/launches',
        name: 'event_prayer_team_assignments',
    )]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(Event $event)
    {
        $form = $this->createForm(EventServersType::class, $event);

        return $this->render('admin/crud/PrayerTeamAssignments/server-assignments.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/launches/{location}',
        name: 'event_launch_prayer_team_assignments',
    )]
    public function prayerTeamAssignments(Event $event, Location $location)
    {
        dd($event, $location);
    }
}
