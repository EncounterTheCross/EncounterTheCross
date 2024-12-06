<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Entity\Leader;
use App\Entity\Location;
use App\Form\EventServersType;
use App\Form\LaunchPointServerPrayerTeamAssignmentsType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(EventRepository $eventRepository)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') || !$this->isGranted('ROLE_LAUNCH_LEADER')) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $event = $eventRepository->findUpcomingEvent();

        return $this->render('admin/crud/PrayerTeamAssignments/launches.html.twig', [
            'launchPoints' => $event->getLaunchPoints(),
            'eventId' => $event->getId(),
        ]);
    }

    #[Route(
        path: '/{event}/launches/{location}',
        name: 'event_launch_prayer_team_assignments',
    )]
    public function prayerTeamAssignments(EntityManagerInterface $entityManager, Request $request, Location $location, ?Event $event = null)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') || !$this->isGranted('ROLE_LAUNCH_LEADER')) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        /** @var Leader $leader */
        $leader = $this->getUser();
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && $leader->getLaunchPoint() !== $location) {
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

        $form = $this->createForm(EventServersType::class, $event, [
            'launchpoint_id' => $location->getId(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
            //            $entityManager->persist($event);
            $entityManager->flush();

            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('task_success');
        }

        return $this->render('admin/crud/PrayerTeamAssignments/server-assignments.html.twig', [
            'form' => $form->createView(),
            'launchPoint' => $location,
        ]);
    }
}
