<?php

namespace App\Taig\Components;

use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Settings\Global\MercureSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
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
        private MercureSettings $mercureSettings,
        private RequestStack $requestStack,
        private RouterInterface $router,
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
    public function processCard()
    {
        $event = $this->participant->getEvent();
        // Store registration data in session
        $this->getRequest()->getSession()->set('registration_data', [
            'event_id' => $event->getId(),
            'registration' => $this->participant,
            'server_check_in' => '/checkin/'.$event->getCheckInToken(),
        ]);

        if ($this->participant->getPaymentMethod() !== 'CARD') {
            $this->participant->setPaymentMethod('CARD');
            $this->entityManager->persist($this->participant);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_registration_payment', [
            'event' => $event->getId()
        ]);
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

        //TODO if the setting if off dont do this
        if ($this->mercureSettings->isActive()) {
            $this->hub->publish($update);
        }

        $this->closeModal();
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param int $status The HTTP status code (302 "Found" by default)
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param int $status The HTTP status code (302 "Found" by default)
     */
    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
}
