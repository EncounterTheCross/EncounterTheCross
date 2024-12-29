<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Entity\Leader;
use App\Entity\Location;
use App\Form\LaunchPointServerPrayerTeamAssignmentsType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(
    path: '/admin/encounter/prayer_team_assignments',
)]
class PrayerTeamAssignmentsController extends AbstractController
{
    #[Route(
        path: '/launches',
        name: 'event_prayer_team_assignments',
    )]
    public function index(EventRepository $eventRepository, Request $request, AdminUrlGenerator $adminUrlGenerator)
    {
        if (!$this->isGranted('ROLE_LEADER')) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $eventId = $request->query->all('routeParams')['event'] ?? null;
        $event = match (true) {
            null !== $eventId => $eventRepository->find($eventId),
            default => $eventRepository->findUpcomingEvent(),
        };

        $launchPoints = $event->getLaunchPoints();
        if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE')) {
            /** @var Leader $user */
            $user = $this->getUser();
            $launchPoint = $launchPoints->filter(function (Location $location) use ($user) {
                return $location->getId() === $user->getLaunchPoint()->getId();
            })->first();

            return $this->redirect(
                $adminUrlGenerator->setRoute(
                    'event_launch_prayer_team_assignments',
                    [
                        'location' => $launchPoint->getId(),
                        'event' => $event->getId(),
                    ]
                )
                    ->generateUrl()
            );
        }

        return $this->render('admin/crud/PrayerTeamAssignments/launches.html.twig', [
            'launchPoints' => $launchPoints,
            'eventId' => $event->getId(),
        ]);
    }

    #[Route(
        path: '/{event}/launches/{location}',
        name: 'event_launch_prayer_team_assignments',
    )]
    public function prayerTeamAssignments(EntityManagerInterface $entityManager, Request $request, Location $location, ?Event $event = null)
    {
        /** @var Leader $leader */
        $leader = $this->getUser();

        if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE') && $leader->getLaunchPoint()->getId() !== $location->getId()) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $servers = $event->getEventParticipants()->filter(function (EventParticipant $participant) use ($location) {
            if ($participant->isAttendee()) {
                return false;
            }

            if ($participant->getLaunchPoint() !== $location) {
                return false;
            }

            return true;
        });

        $servers->map(function (EventParticipant $server) {
            if (0 === $server->getEventPrayerTeamServers()->count()) {
                $assignment = new EventPrayerTeamServer();
                $server->addEventPrayerTeamServer($assignment);
            }
        });

        $form = $this->createForm(LaunchPointServerPrayerTeamAssignmentsType::class, ['eventParticipants' => $servers]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData()['eventParticipants'] as $server) {
                $entityManager->persist($server);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Assignments Added');

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/crud/PrayerTeamAssignments/server-assignments.html.twig', [
            'form' => $form->createView(),
            'launchPoint' => $location,
        ]);
    }
}
