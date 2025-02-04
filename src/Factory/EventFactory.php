<?php

namespace App\Factory;

use App\Entity\Event;
use DateInterval;
use DateTime;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class EventFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
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

    public static function class(): string
    {
        return Event::class;
    }

    protected function defaults(): array|callable
    {
        $start = self::faker()->dateTimeBetween('+1 month', '+1 year');
        $end = new DateTime($start->format('Y-m-d H:i:s'));
        $end->add(new DateInterval('P2D'));

        return [
            'createdAt' => self::faker()->dateTime(),
            'end' => $end,
            'location' => LocationFactory::new('event'),
            'name' => $start->format('M Y').' Men\'s Encounter',
            'registrationDeadLineServers' => $start->add(new DateInterval('P2W')), // self::faker()->dateTime(),

            'start' => $start,
            'updatedAt' => self::faker()->dateTime(),
            'launchPoints' => LocationFactory::allLaunchPoints(),
            'price' => self::faker()->randomFloat(2),
            'active' => true,
            'registrationOpen' => true,
            'checkInToken' => bin2hex(random_bytes(32)),
        ];
    }
}
