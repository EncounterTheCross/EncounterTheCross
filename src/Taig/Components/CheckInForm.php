<?php

namespace App\Taig\Components;

use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Twig\Environment;

#[AsLiveComponent(template: 'components/Taig/CheckInForm.html.twig')]
final class CheckInForm
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(updateFromParent: true)]
    public ?EventParticipant $participant = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private HubInterface $hub,
        private Environment $twig,
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
        // Include the participant ID in the event to target specific modals
        $this->dispatchBrowserEvent('close-modal', [
            'participantId' => $this->participant->getId(),
        ]);

        // Use emitUp instead of emit to limit the scope of the event
        $this->emitUp('server-checked-in', [
            'participantId' => $this->participant->getId(),
        ]);
    }

    private function createPrayerTeam(): EventPrayerTeamServer
    {
        $prayerTeam = new EventPrayerTeamServer();
        $prayerTeam
            ->setEvent($this->participant->getEvent())
            ->setEventParticipant($this->participant)
        ;

        return $prayerTeam;
    }

    private function checkInParticipant(): void
    {
        $registration = $this->participant->getCurrentEventPrayerTeamServer();

        if (null === $registration) {
            $registration = $this->createPrayerTeam();
        }

        $registration->setCheckedIn(true);

        $this->entityManager->persist($registration);
        $this->entityManager->flush();

        // Verify the template is rendering with the correct participant
        $renderedContent = $this->twig->render('tailwind/Components/streams/ServerRegistrationDetailRow.stream.html.twig', [
            'participant' => $this->participant,
        ]);

        $update = new Update(
            'server-training-checkin',
            json_encode([
                'participant_id' => $this->participant->getId(),
                'checkedIn' => $registration->isCheckedIn(),
                'prayerTeam' => $registration->getPrayerTeam()?->getName(),
                'paymentMethod' => $this->participant->getPaymentMethod(),
                'isPaid' => $this->participant->isPaid(),
            ]),
        );

        $this->hub->publish($update);

        $this->closeModal();
    }
}
