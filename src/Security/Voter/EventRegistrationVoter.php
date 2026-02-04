<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\Leader;
use App\Repository\EventRepository;
use App\Settings\Global\SystemSettings;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Tzunghaor\SettingsBundle\Service\SettingsService;
use App\Settings\Global\RegistrationSettings;

class EventRegistrationVoter extends Voter
{
    public const SERVER = 'registration_event_server';
    public const ATTENDEE = 'registration_event_attendee';

    public function __construct(
        private EventRepository $eventRepository,
        private SettingsService $settingsService,
        private RequestStack $requestStack,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SERVER, self::ATTENDEE])
            && $subject instanceof Event;
    }

    /**
     * @param Event $subject
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $upcomingEvent = $this->eventRepository->findUpcomingEvent();

        if ($upcomingEvent->getId() !== $subject->getId() || !$upcomingEvent->isRegistrationStarted()) {
            $this->getFlashBag()->add('error', 'Registration is not opened for this event.');

            return false;
        }

        $registrationSettings = $this->settingsService->getSection(RegistrationSettings::class);

        // Manually Close Registration
        if (
            new DateTime() < $subject->getStart() 
            && !$subject->isRegistrationOpen() 
            // && !$registrationSettings->isWaitlistEnabled()
        ) {
            // TODO: send redirect to new registration with message that registration has filled up.
            //       display the date the next event registration will open.
            // $this->getFlashBag()->add('error', 'This event has had a major increase in registration. To make sure all are able to attend please reach us via email or try again later.');
            $this->getFlashBag()->add('error', 'This event has reached capacity and registration is currently closed.  Limited space may become available, so please be in contact with the man who invited you or save the date for the next Men’s Encounter.');

            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SERVER:
                
                $totalServers = $subject->getServers()->count();
                if ($subject->getMaxServers() !== null && $totalServers >= $subject->getMaxServers() && !$registrationSettings->isWaitlistEnabled()) {
                    $this->getFlashBag()->add('error', 'Server registration is full for this Encounter.');  
                    return false;
                }

                if (!$this->getGlobalSettings()->isRegistrationDeadlineInforced()) {
                    return true;
                }

                if (new DateTime() < $subject->getRegistrationDeadLineServers()) {
                    return true;
                }

                $this->getFlashBag()->add('error', 'The deadline for server registration has passed.');

                break;
            case self::ATTENDEE:
                if (!$this->getGlobalSettings()->isRegistrationDeadlineInforced()) {
                    return true;
                }

                if (new DateTime() > $subject->getStart()) {
                    $this->getFlashBag()->add('error', 'Registration has expired for this Encounter.');

                    return $token->getUser() instanceof Leader;
                }

                return true;
        }

        return false;
    }

    private function getFlashBag(): FlashBagInterface
    {
        return $this->requestStack->getSession()->getFlashBag(); // phpstan-ignore-line
    }

    private function getGlobalSettings(): object
    {
        return $this->settingsService->getSection(SystemSettings::class);
    }
}
