<?php

namespace App\Controller;

use App\Entity\EventParticipant;
use App\Form\ServerTrainingCheckInType;
use App\Repository\EventRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/{token}/{participant}', name: 'app_server_checkin_form')]
    public function checkin(string $token, EventParticipant $participant, EventRepository $eventRepository)
    {
        $event = $eventRepository->findOneBy(['checkInToken' => $token]);
        if ($participant->getEvent()->getId() !== $event->getId()) {
            $this->addFlash('error', 'Checking into the incorrect Event, Try again.');

            return $this->redirectToRoute('app_server_checkin_register', ['token' => $token]);
        }

        $form = $this->createServerCheckinForm($participant);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form, $form->getData());
        }

        return new Response(
            $this->renderComponent('Taig:Modal', [
                'participant' => $participant,
                'isOpen' => true,
            ])
        );
    }

    private function createServerCheckinForm(?EventParticipant $participant = null): FormInterface
    {
        $participant = $participant ?? new EventParticipant();

        return $this->createForm(ServerTrainingCheckInType::class, $participant, [
            //            'action' => $participant->getId() ? $this->generateUrl('app_voyage_edit', ['id' => $participant->getId()]) : $this->generateUrl('app_voyage_new'),
        ]);
    }
}
