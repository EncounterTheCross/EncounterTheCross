<?php

namespace App\Factory;

use App\Entity\EventParticipant;
use App\Repository\EventParticipantRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<EventParticipant>
 *
 * @method        EventParticipant|Proxy                     create(array|callable $attributes = [])
 * @method static EventParticipant|Proxy                     createOne(array $attributes = [])
 * @method static EventParticipant|Proxy                     find(object|array|mixed $criteria)
 * @method static EventParticipant|Proxy                     findOrCreate(array $attributes)
 * @method static EventParticipant|Proxy                     first(string $sortedField = 'id')
 * @method static EventParticipant|Proxy                     last(string $sortedField = 'id')
 * @method static EventParticipant|Proxy                     random(array $attributes = [])
 * @method static EventParticipant|Proxy                     randomOrCreate(array $attributes = [])
 * @method static EventParticipantRepository|RepositoryProxy repository()
 * @method static EventParticipant[]|Proxy[]                 all()
 * @method static EventParticipant[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static EventParticipant[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static EventParticipant[]|Proxy[]                 findBy(array $attributes)
 * @method static EventParticipant[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static EventParticipant[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
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
    protected function initialize(): self
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
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'state' => self::faker()->state(),
            'type' => $type,
            'updatedAt' => self::faker()->dateTime(),
            'zipcode' => self::faker()->postcode(),
            'event' => EventFactory::randomOrCreate(),
            'paymentMethod' => self::faker()->randomElement(EventParticipant::PAYMENT_METHODS),
        ], $typeDefaults);
    }
}
