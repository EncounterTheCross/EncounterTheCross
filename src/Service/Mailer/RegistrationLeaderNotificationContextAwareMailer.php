<?php

namespace App\Service\Mailer;

use App\Entity\EventParticipant;
use App\Repository\LeaderRepository;
use App\Settings\Global\SystemSettings;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface as Mailer;
use Symfony\Component\Mime\Email;
use Tzunghaor\SettingsBundle\Service\SettingsService;

final class RegistrationLeaderNotificationContextAwareMailer extends AbstractContextAwareMailer
{
    /**
     * This array will contain the KEY names that the context will require and the VALUE will be the object type that the context should have.
     */
    private const CONTEXT_REQUIREMENTS = [
        self::CONTEXT_REGISTRATION_OBJECT => EventParticipant::class,
    ];

    public const CONTEXT_REGISTRATION_OBJECT = 'registration';

    private SystemSettings $settings;

    public function __construct(
        Mailer $mailer,
        LoggerInterface $logger,
        private LeaderRepository $leaderRepository,
        SettingsService $globalSettings,
    ) {
        parent::__construct($mailer, $logger);
        $this->settings = $globalSettings->getSection(SystemSettings::class);
    }

    protected function configureEmail(TemplatedEmail|Email $email): TemplatedEmail|Email
    {
        assert($email instanceof TemplatedEmail);
        $email
            ->subject('Registration for Encounter')
            ->htmlTemplate('email/registration/notification.html.twig')
        ;

        if (array_key_exists(self::CONTEXT_REGISTRATION_OBJECT, $email->getContext())) {
            $registration = $email->getContext()[self::CONTEXT_REGISTRATION_OBJECT];
            assert($registration instanceof EventParticipant);
            $event = $registration->getEvent();

            // Create a more detailed subject line
            $email->subject(sprintf(
                '%s Registration for %s',
                ucfirst($registration->isServer() ? EventParticipant::TYPE_SERVER : EventParticipant::TYPE_ATTENDEE),
                $event->getName() ?? 'Registration for Encounter'
            ));
        }

        if ($this->settings->isDebugEmails()) {
            $toEmails = array_map(function (string $email) {
                return ['email' => $email];
            }, $this->settings->getDebugEmailAddresses());
            $email->to(...$this->createToAddresses($toEmails));

            return $email;
        }

        $toEmails = $this->leaderRepository->findAllLeadersWithNotificationOnAndActive();
        
        $email->to(...$this->createToAddresses($toEmails));

        return $email;
    }
}
