<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\EventParticipant;
use App\Form\ServerTrainingCheckInType;
use App\Repository\EventRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkin')]
class CheckInController extends AbstractController
{
    #[Route('/{token}', name: 'app_server_checkin_register')]
    public function encounterList(string $token, EventRepository $eventRepository)
    {
        $event = $eventRepository->findOneBy(['checkInToken' => $token]);

        //        dd($event->getLaunchPoints()->first()->getEventAttendees()->first()->getEventPrayerTeamServers());
        return $this->render('tailwind/checkin.html.twig', [
            'launches' => $event->getLaunchPoints(),
            'event' => $event,
        ]);
    }

    private function createServerCheckinForm(?EventParticipant $participant = null): FormInterface
    {
        $participant = $participant ?? new EventParticipant();

        return $this->createForm(ServerTrainingCheckInType::class, $participant, [
            //            'action' => $participant->getId() ? $this->generateUrl('app_voyage_edit', ['id' => $participant->getId()]) : $this->generateUrl('app_voyage_new'),
        ]);
    }
}
