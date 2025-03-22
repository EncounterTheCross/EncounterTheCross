<?php

namespace App\Taig\Components;

use App\Entity\EventParticipant;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ServerRegistrationDetailRow
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public EventParticipant $participant;

    #[LiveListener('server-checked-in')]
    public function serverCheckedIn(
        #[LiveArg] int $participantId,
    ): void {
        // Only re-render if this is the relevant participant
        if ($participantId === $this->participant->getId()) {
            // Refresh only the necessary component
        }
    }
}
