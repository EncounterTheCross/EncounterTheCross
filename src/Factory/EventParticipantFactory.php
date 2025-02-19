<?php

namespace App\Factory;

use App\Entity\EventParticipant;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class EventParticipantFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function server(): EventParticipantFactory
    {
        return $this->with([
            'type' => EventParticipant::TYPE_SERVER,
            'attendeeContactPerson' => null,
        ]);
    }

    public function attendee(): EventParticipantFactory
    {
        return $this->with([
            'type' => EventParticipant::TYPE_ATTENDEE,
            'attendeeContactPerson' => $this->findByPersonDetailsOrCreate(),
        ]);
    }

    protected function findByPersonDetailsOrCreate($email = null, $phone = null)
    {
        if (null === $email) {
            $email = self::faker()->email();
        }
        if (null === $phone) {
            $phone = self::faker()->phoneNumber();
        }

        return ContactPersonFactory::findByPersonDetailsOrCreate($email, $phone);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(EventParticipant $eventParticipant): void {})
        ;
    }

    public static function class(): string
    {
        return EventParticipant::class;
    }

    protected function defaults(): array|callable
    {
        $type = self::faker()->randomElement(EventParticipant::TYPES());

        $typeDefaults = [];
        if (EventParticipant::TYPE_ATTENDEE === $type) {
            // TODO: Fill out the required fields per `type` (ATTENDEE)
            $contactEmail = self::faker()->email();
            $contactPhone = self::faker()->phoneNumber();
            $typeDefaults['attendeeContactPerson'] = $this->findByPersonDetailsOrCreate($contactEmail, $contactPhone);
        }
        if (EventParticipant::TYPE_SERVER === $type) {
            // TODO: Fill out the required fields per `type` (SERVER)
        }

        $eventLaunchPoints = EventFactory::randomOrCreate()->getLaunchPoints();

        return array_merge([
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'createdAt' => self::faker()->dateTime(),
            'launchPoint' => self::faker()->randomElement($eventLaunchPoints),
            'line1' => self::faker()->streetAddress(),
            'person' => PersonFactory::findByEmailOrPhoneOrCreate(
                self::faker()->email(),
                self::faker()->phoneNumber()
            ),

            'state' => self::faker()->state(),
            'type' => $type,
            'updatedAt' => self::faker()->dateTime(),
            'zipcode' => self::faker()->postcode(),
            'event' => EventFactory::randomOrCreate(),
            'paymentMethod' => self::faker()->randomElement(EventParticipant::PAYMENT_METHODS),
        ], $typeDefaults);
    }
}
