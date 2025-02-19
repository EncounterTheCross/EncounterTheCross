<?php

namespace App\Factory;

use App\Entity\Location;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class LocationFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function launchPoint(): self
    {
        return $this->with(['type' => Location::TYPE_LAUNCH_POINT, 'pinColor' => self::faker()->hexColor()]);
    }

    public function event(): self
    {
        return $this->with(['type' => Location::TYPE_EVENT]);
    }

    public static function allLaunchPoints($min = 1)
    {
        if ($launchPoints = self::findBy(['type' => Location::TYPE_LAUNCH_POINT])) {
            return $launchPoints;
        }

        return LocationFactory::new('launchPoint')->many($min);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Location $location): void {})
        ;
    }

    public static function class(): string
    {
        return Location::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'createdAt' => self::faker()->dateTime(),
            'line1' => self::faker()->streetAddress(),
            'name' => self::faker()->company(),

            'state' => self::faker()->state(),
            'type' => self::faker()->randomElement(Location::TYPES()),
            'updatedAt' => self::faker()->dateTime(),
            'zipcode' => self::faker()->postcode(),
        ];
    }
}
