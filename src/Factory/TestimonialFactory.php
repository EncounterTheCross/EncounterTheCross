<?php

namespace App\Factory;

use App\Entity\Testimonial;
use App\Repository\TestimonialRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Testimonial>
 *
 * @method        Testimonial|Proxy                     create(array|callable $attributes = [])
 * @method static Testimonial|Proxy                     createOne(array $attributes = [])
 * @method static Testimonial|Proxy                     find(object|array|mixed $criteria)
 * @method static Testimonial|Proxy                     findOrCreate(array $attributes)
 * @method static Testimonial|Proxy                     first(string $sortedField = 'id')
 * @method static Testimonial|Proxy                     last(string $sortedField = 'id')
 * @method static Testimonial|Proxy                     random(array $attributes = [])
 * @method static Testimonial|Proxy                     randomOrCreate(array $attributes = [])
 * @method static TestimonialRepository|RepositoryProxy repository()
 * @method static Testimonial[]|Proxy[]                 all()
 * @method static Testimonial[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Testimonial[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Testimonial[]|Proxy[]                 findBy(array $attributes)
 * @method static Testimonial[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Testimonial[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
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
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'email' => self::faker()->email(),
            'attendedAt' => self::faker()->city(),
            'city' => self::faker()->city(),
            'sharable' => 1,
            'approved' => self::faker()->boolean(),
        ];
    }
}
