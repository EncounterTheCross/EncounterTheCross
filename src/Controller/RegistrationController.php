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
use Symfony\Component\Form\Form;
use Stripe\Stripe;
use Stripe\PaymentIntent;

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

        return $this->render('frontend/events/list.html.twig', [
            'events' => [$event],
            //            'events' => $events,
            'strict_registration' => $strictRegistration,
        ]);
    }

    #[Route('/register/{event}/attendee', name: 'app_registration_attendee_formentry')]
    public function attendeeRegistration(Event $event, Request $request)
    {
        if (!$this->isGranted(EventRegistrationVoter::ATTENDEE, $event)) {
            return $this->redirectToRoute('app_registration_list');
        }

        $eventRegistration = new EventParticipant();
        $eventRegistration->setEvent($event);
        $form = $this->createForm(AttendeeEventParticipantType::class, $eventRegistration);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var EventParticipant $eventRegistration */
            //            $eventRegistration = $form->getData();
            $eventRegistration->setPerson(
                $this->personManager->exists(
                    $form->get('person')->getData()
                )
            );

            //            $eventRegistration->setEvent($event);

            $this->eventParticipantRepository->save(
                $eventRegistration,
                true
            );

            // send email notification and thank you
            $this->sendEmails($eventRegistration);

            return $this->processPayment($form, $eventRegistration, $request);
        }

        return $this->render('frontend/events/attendee.regestration.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'stripe_public_key' => 'pk_test_51SDs5pK2q4IHjfeAsmi6s7hmn4fo0wSpDFyzaCZP7VF2G7o7vhpJHXUbp9uwrSAjrQuU3H5oP9BVL8uRoxM1UgRO00x8fqsjmX',
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
        $form = $this->createForm(ServerEventParticipantType::class, $eventRegistration);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var EventParticipant $eventRegistration */
            $eventRegistration->setPerson(
                $this->personManager->exists(
                    $form->get('person')->getData()
                )
            );

            $this->eventParticipantRepository->save(
                $eventRegistration,
                true
            );

            // send email notification and thank you
            $this->sendEmails($eventRegistration);

            return $this->processPayment($form, $eventRegistration, $request);
        }

        return $this->render('frontend/events/server.regestration.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'stripe_public_key' => 'test',
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/register/thank-you', name: 'app_registration_registrationthankyou')]
    public function registrationThankYou()
    {
        return $this->render('frontend/events/submitted.regestration.html.twig', []);
    }

    #[Route('/register/{event}/payment', name: 'app_registration_payment')]
    public function payment(Event $event, Request $request)
    {
        // Get registration data from session
        $registrationData = $request->getSession()->get('registration_data');
        
        if (!$registrationData || $registrationData['event_id'] !== $event->getId()) {
            $this->addFlash('error', 'Registration session expired. Please start over.');
            return $this->redirectToRoute('app_registration_attendee_formentry', ['event' => $event->getId()]);
        }

        // Create PaymentIntent
        Stripe::setApiKey($this->getStripeSettings()->getSecretKey());
        
        try {
            $eventAmount = $event->getPrice() * 100; // Convert to cents
            $paymentIntent = PaymentIntent::create([
                // TODO: add % of stripe fees as well
                'amount' => $eventAmount, // Convert to cents
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'event_id' => $event->getId(),
                    'event_name' => $event->getName(),
                    'registration_id' => $registrationData['registration']->getId(),
                ],
            ]);

            return $this->render('frontend/events/payment.html.twig', [
                'event' => $event,
                'registration' => $registrationData['registration'],
                'stripe_public_key' => $this->getStripeSettings()->getPublicKey(),
                'client_secret' => $paymentIntent->client_secret,
                'event_amount' => $eventAmount, // Convert to cents
            ], new Response(null, 200));
        } catch (\Exception $e) {
            // if we have made it this far we should have samed the registration data.
            // now we just need to collect payment.
            $this->addFlash('error', 'Failed to initialize payment: ' . $e->getMessage());
            return $this->redirectToRoute('app_registration_registrationthankyou');
        }
    }

    protected function sendEmails(EventParticipant $registration): void
    {
        if (!$this->getGlobalSettings()->isEmailNotificationsTurnedOn()) {
            return;
        }

        $toEmail = [new Address($registration->getPerson()->getEmail(), $registration->getFullName())];
        $this->registrationThankYouMailer->send(
            toEmails: $toEmail, context: ['registration' => $registration],
        );
        $this->registrationNotificationMailer->send(
            context: ['registration' => $registration]
        );
    }

    private function processPayment(Form $form,EventParticipant $registration, Request $request)
    {
        $paymentMethod = $form->get('paymentMethod')->getData();

        // If payment method is card, store data in session and redirect to payment page
        if ($paymentMethod !== 'card') {
            return $this->redirectToRoute('app_registration_registrationthankyou');
        }

        $event = $registration->getEvent();

        // Store registration data in session
        $request->getSession()->set('registration_data', [
            'event_id' => $event->getId(),
            'registration' => $registration,
        ]);
        
        return $this->redirectToRoute('app_registration_payment', [
            'event' => $event->getId()
        ]);
    }
}
