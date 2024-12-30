<?php

namespace App\Twig\Components;

use App\Repository\LeaderRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('contact_event_leaders')]
final class ContactEventLeadersComponent
{
    public function __construct(
        private LeaderRepository $leaderRepository,
    ) {
    }

    public function getEventLeaders()
    {
        $eventLeaders = $this->leaderRepository->findEventLeaders();

        return $eventLeaders;
    }
}
