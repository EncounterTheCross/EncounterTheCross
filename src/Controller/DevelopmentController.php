<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Form\EventServersType;
use App\Repository\EventParticipantRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class DevelopmentController extends AbstractController
{
    #[Route('/dev/ep')]
    public function prayerTeams(
        EventParticipantRepository $eventParticipantRepository,
        EventRepository $eventRepository,
        Request $request,
    ) {
        $currentEvent = $eventRepository->findUpcomingEvent();
        $launchPoints = $currentEvent->getLaunchPoints();
        $launchPointServers = $launchPoints->map(function ($launchPoint) use ($currentEvent) {
            $servers = $currentEvent->getEventParticipants()->map(function (EventParticipant $eventParticipant) {
                if ($eventParticipant->isAttendee()) {
                    return null;
                }

                return $eventParticipant;
            });

            return array_values($servers->toArray());
        });

        $currentEvent->clearEventParticipants();
        $serverTrainting = new Event();
        foreach ($launchPointServers as $launchPoint) {
            foreach ($launchPoint as $server) {
                if (null === $server) {
                    continue;
                }
                $currentEvent->addEventParticipant($server);
            }
        }

        $form = $this->createForm(EventServersType::class, $currentEvent);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... do your form processing, like saving the Task and Tag entities
            dd($form->getData());
        }

        return $this->render('admin/crud/PrayerTeamAssignments/server-assignments.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        '/dev',
        name: 'app_dev',
        env: 'dev',
    )]
    public function index(EventRepository $repo, KernelInterface $app)
    {
        dd($app->getBuildDir(), $app->getProjectDir());
        dd($repo->findUpcomingEvent()?->getPrice());

        dd($this->container->get('tzunghaor_settings.settings_service.global'));
        dump($repo->findAllLeadersWithNotificationOnAndActive());
        dd('test');
    }
}
