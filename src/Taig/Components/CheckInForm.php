<?php

namespace App\Taig\Components;

use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Taig/CheckInForm.html.twig')]
final class CheckInForm
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(updateFromParent: true)]
    public ?EventParticipant $participant = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveAction]
    public function atEncounter()
    {
        $this->checkInParticipant();
    }

    #[LiveAction]
    public function paid(): void
    {
        $this->participant->setPaid(true);

        $this->entityManager->persist($this->participant);
        $this->checkInParticipant();
    }

    #[LiveAction]
    public function closeModal(): void
    {
        $this->dispatchBrowserEvent('close-modal');
        $this->emit('server-checked-in', [
            'participant' => $this->participant->getId(),
        ]);
    }

    private function checkInWithoutPrayerTeam(): void
    {
        $prayerTeam = new EventPrayerTeamServer();
        $prayerTeam
            ->setEvent($this->participant->getEvent())
            ->setEventParticipant($this->participant)
            ->setCheckedIn(true)
        ;

        $this->entityManager->persist($prayerTeam);
        $this->entityManager->flush();

        $this->closeModal();
    }

    private function checkInParticipant(): void
    {
        $registration = $this->participant->getCurrentEventPrayerTeamServer();

        if (null === $registration) {
            $this->checkInWithoutPrayerTeam();

            return;
        }

        $registration->setCheckedIn(true);

        $this->entityManager->persist($registration);
        $this->entityManager->flush();

        $this->closeModal();
    }
}
