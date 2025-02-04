<?php

namespace App\Factory;

use App\Entity\Testimonial;
use App\Repository\TestimonialRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;


final class TestimonialFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
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
            // ->afterInstantiate(function(Testimonial $testimonial): void {})
        ;
    }

    public static function class(): string
    {
        return Testimonial::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->firstNameMale(),
            'quote' => self::faker()->paragraph(),

            'email' => self::faker()->email(),
            'attendedAt' => self::faker()->city(),
            'city' => self::faker()->city(),
            'sharable' => 1,
            'approved' => self::faker()->boolean(),
        ];
    }
}
