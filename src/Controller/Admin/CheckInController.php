<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkin')]
class CheckInController extends AbstractController
{
    #[Route('/{token}', name: 'app_server_checkin_register')]
    public function encounterList(string $token, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->findByServerCheckInToken($token);
        
        if (!$event) {
            throw $this->createNotFoundException('Event is not Active or does not exist.');
        }

        return $this->render('tailwind/checkin.html.twig', [
            'launches' => $event->getLaunchPoints(),
            'event' => $event,
            'useMercure' => $this->getMercureSettings()->isActive(),
        ]);
    }
}
