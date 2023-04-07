<?php

namespace App\Factory;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Event>
 *
 * @method        Event|Proxy create(array|callable $attributes = [])
 * @method static Event|Proxy createOne(array $attributes = [])
 * @method static Event|Proxy find(object|array|mixed $criteria)
 * @method static Event|Proxy findOrCreate(array $attributes)
 * @method static Event|Proxy first(string $sortedField = 'id')
 * @method static Event|Proxy last(string $sortedField = 'id')
 * @method static Event|Proxy random(array $attributes = [])
 * @method static Event|Proxy randomOrCreate(array $attributes = [])
 * @method static EventRepository|RepositoryProxy repository()
 * @method static Event[]|Proxy[] all()
 * @method static Event[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Event[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Event[]|Proxy[] findBy(array $attributes)
 * @method static Event[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Event[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class EventFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * TODO remove row pointer once DoctrineEvent Hook is used
     */
    protected function getDefaults(): array
    {
        $start = self::faker()->dateTimeBetween('+1 month','+1 year');
        return [
            'createdAt' => self::faker()->dateTime(),
            'end' => $start->modify('+3 days'),
            'location' => LocationFactory::new('event'),
            'name' => self::faker()->text(255),
            'registrationDeadLineServers' => $start->modify('-2 weeks'),//self::faker()->dateTime(),
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'start' => $start,
            'updatedAt' => self::faker()->dateTime(),
            'launchPoints' => LocationFactory::allLaunchPoints(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Event $event): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Event::class;
    }
}
