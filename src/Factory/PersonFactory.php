<?php

namespace App\Factory;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Person>
 *
 * @method        Person|Proxy                     create(array|callable $attributes = [])
 * @method static Person|Proxy                     createOne(array $attributes = [])
 * @method static Person|Proxy                     find(object|array|mixed $criteria)
 * @method static Person|Proxy                     findOrCreate(array $attributes)
 * @method static Person|Proxy                     first(string $sortedField = 'id')
 * @method static Person|Proxy                     last(string $sortedField = 'id')
 * @method static Person|Proxy                     random(array $attributes = [])
 * @method static Person|Proxy                     randomOrCreate(array $attributes = [])
 * @method static PersonRepository|RepositoryProxy repository()
 * @method static Person[]|Proxy[]                 all()
 * @method static Person[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Person[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Person[]|Proxy[]                 findBy(array $attributes)
 * @method static Person[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Person[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class PersonFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function findByEmailOrPhone($email, $phone)
    {
        if ($person = self::findBy([
            'email' => $email,
        ])) {
            return $person[0];
        }

        if ($person = self::findBy([
            'phone' => $phone,
        ])) {
            return $person[0];
        }

        return null;
    }

    public static function findByEmailOrPhoneOrCreate($email, $phone)
    {
        if ($person = self::findByEmailOrPhone($email, $phone)) {
            return $person;
        }

        return self::new([
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Person $person): void {})
        ;
    }

    public static function class(): string
    {
        return Person::class;
    }

    protected function defaults(): array|callable
    {
        $email = self::faker()->boolean(80) ? self::faker()->email() : null;
        $phone = self::faker()->boolean(80) ? self::faker()->phoneNumber() : null;
        $data = [
            'createdAt' => self::faker()->dateTime(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'updatedAt' => self::faker()->dateTime(),
        ];

        if ($email) {
            $data['email'] = $email;
        }

        if ($phone) {
            $data['phone'] = $phone;
        }

        return $data;
    }
}
