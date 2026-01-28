<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 6/19/23
 * Project: EncounterTheCross
 * File Name: RegistrationController.php
 */

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Form\AttendeeEventParticipantType;
use App\Form\ServerEventParticipantType;
use App\Repository\EventParticipantRepository;
use App\Repository\EventRepository;
use App\Security\Voter\EventRegistrationVoter;
use App\Service\Mailer\RegistrationLeaderNotificationContextAwareMailer;
use App\Service\Mailer\RegistrationThankYouContextAwareMailer;
use App\Service\PersonManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SpamDetection\SpamDetectionService;
use App\Enum\EventParticipantStatusEnum;

#[Route(
    '/men'
)]
class RegistrationController extends AbstractController
{
    public function __construct(
        private PersonManager $personManager,
        private EventParticipantRepository $eventParticipantRepository,
        private RegistrationLeaderNotificationContextAwareMailer $registrationNotificationMailer,
        private RegistrationThankYouContextAwareMailer $registrationThankYouMailer,
        private SpamDetectionService $spamDetectionService,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/register', name: 'app_registration_list')]
    public function encounterList(EventRepository $eventRepository)
    {
        $event = $eventRepository->findUpcomingEvent();
        //        $events = $eventRepository->findAll();

        $strictRegistration = $this->getGlobalSettings()->isRegistrationDeadlineInforced();
        $waitlistEnabled = $this->getRegistrationSettings()->isWaitlistEnabled()
            && !$event?->isRegistrationOpen()
        ;

        return $this->render('frontend/events/list.html.twig', [
            'events' => [$event],
            //            'events' => $events,
            'waitlist_enabled' => $waitlistEnabled,
            'strict_registration' => $strictRegistration,
        ]);
    }

    #[Route('/register/{event}/attendee', name: 'app_registration_attendee_formentry')]
    public function attendeeRegistration(Event $event, Request $request)
    {
        if (!$this->isGranted(EventRegistrationVoter::ATTENDEE, $event)) {
            return $this->redirectToRoute('app_registration_list');
        }
        $waitlistEnabled = $this->getRegistrationSettings()->isWaitlistEnabled()
            && !$event->isRegistrationOpen();

        $eventRegistration = new EventParticipant();
        $eventRegistration->setEvent($event);
        $form = $this->createForm(AttendeeEventParticipantType::class, $eventRegistration, [
            'antispam_profile' => 'default',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var EventParticipant $eventRegistration */
            //            $eventRegistration = $form->getData();
            $eventRegistration->setPerson(
                $this->personManager->exists(
                    $form->get('person')->getData()
                )
            );

            $spamDetails = $this->spamDetectionService->getSpamDetails(
                $eventRegistration->getSpamSerialization()
            );

            $eventRegistration->setRawSpamDetails(
                $spamDetails
            );

            if($waitlistEnabled) {
                //Update status to waitlisted
                $eventRegistration->setStatus(EventParticipantStatusEnum::WAITLISTED->value);
            }

            //            $eventRegistration->setEvent($event);

            $this->eventParticipantRepository->save(
                $eventRegistration,
                true
            );

            // send email notification and thank you
            $this->sendEmails($eventRegistration);

            if($waitlistEnabled) {
                return $this->redirectToRoute('app_registration_registrationwaitingthankyou');
            }

            return $this->redirectToRoute('app_registration_registrationthankyou');
        }

        return $this->render('frontend/events/attendee.regestration.html.twig', [
            'event' => $event,
            'waitlist_enabled' => $waitlistEnabled,
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/register/{event}/server', name: 'app_registration_server_formentry')]
    public function serverRegistration(Event $event, Request $request)
    {
        if (!$this->isGranted(EventRegistrationVoter::SERVER, $event)) {
            return $this->redirectToRoute('app_registration_list');
        }

        $eventRegistration = new EventParticipant();
        $eventRegistration->setEvent($event);
        $form = $this->createForm(ServerEventParticipantType::class, $eventRegistration, [
            'antispam_profile' => 'default',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var EventParticipant $eventRegistration */
            $eventRegistration->setPerson(
                $this->personManager->exists(
                    $form->get('person')->getData()
                )
            );

            $spamDetails = $this->spamDetectionService->getSpamDetails(
                $eventRegistration->getSpamSerialization()
            );

            $eventRegistration->setRawSpamDetails(
                $spamDetails
            );

            $this->eventParticipantRepository->save(
                $eventRegistration,
                true
            );

            // send email notification and thank you
            $this->sendEmails($eventRegistration);

            return $this->redirectToRoute('app_registration_registrationthankyou');
        }

        return $this->render('frontend/events/server.regestration.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/register/confirmation/thank-you', name: 'app_registration_registrationthankyou')]
    public function registrationConfirmationThankYou()
    {
        return $this->render('frontend/events/submitted.regestration.html.twig', [
            'waitlist_enabled' => false,
        ]);
    }

    #[Route('/register/waiting/thank-you', name: 'app_registration_registrationwaitingthankyou')]
    public function registrationWaitingThankYou()
    {
        return $this->render('frontend/events/submitted.regestration.html.twig', [
            'waitlist_enabled' => true,
        ]);
    }

    protected function sendEmails(EventParticipant $registration): void
    {
        if (!$this->getGlobalSettings()->isEmailNotificationsTurnedOn()) {
            return;
        }

        $waitlistEnabled = $this->getRegistrationSettings()->isWaitlistEnabled()
            && !$registration->getEvent()->isRegistrationOpen();

        // Do not send if this is spam
        if ($registration->isSpam()) {
            //Update status to spam
            $registration->setStatus(EventParticipantStatusEnum::SPAM);
            $this->eventParticipantRepository->save($registration, true);

            return;
        }

        $toEmail = [new Address($registration->getPerson()->getEmail(), $registration->getFullName())];
        
        $this->registrationThankYouMailer->send(
            toEmails: $toEmail, context: ['registration' => $registration, 'waitlist_enabled' => $waitlistEnabled],
        );
        if(!$waitlistEnabled) {
            $this->registrationNotificationMailer->send(
                context: ['registration' => $registration]
            );
        }
    }
}
