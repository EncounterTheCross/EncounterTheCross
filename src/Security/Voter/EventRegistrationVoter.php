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

        if ($upcomingEvent->getId() !== $subject->getId()) {
            $this->getFlashBag()->add('error', 'Registration is not opened for this event.');

            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::SERVER:
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
