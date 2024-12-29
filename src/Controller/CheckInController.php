<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkin')]
class CheckInController extends AbstractController
{
    #[Route('/{token}', name: 'app_server_checkin_register')]
    public function encounterList(string $token, EventRepository $eventRepository)
    {
        dd($eventRepository->findOneBy(['checkInToken' => $token]));
    }
}
