<?php

namespace App\Factory;

use App\Entity\ContactPerson;
use App\Repository\ContactPersonRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ContactPerson>
 *
 * @method        ContactPerson|Proxy                     create(array|callable $attributes = [])
 * @method static ContactPerson|Proxy                     createOne(array $attributes = [])
 * @method static ContactPerson|Proxy                     find(object|array|mixed $criteria)
 * @method static ContactPerson|Proxy                     findOrCreate(array $attributes)
 * @method static ContactPerson|Proxy                     first(string $sortedField = 'id')
 * @method static ContactPerson|Proxy                     last(string $sortedField = 'id')
 * @method static ContactPerson|Proxy                     random(array $attributes = [])
 * @method static ContactPerson|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ContactPersonRepository|RepositoryProxy repository()
 * @method static ContactPerson[]|Proxy[]                 all()
 * @method static ContactPerson[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ContactPerson[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ContactPerson[]|Proxy[]                 findBy(array $attributes)
 * @method static ContactPerson[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ContactPerson[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ContactPersonFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function findByPersonDetailsOrCreate($email, $phone)
    {
        return self::new(['details' => PersonFactory::findByEmailOrPhoneOrCreate($email, $phone)]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ContactPerson $contactPerson): void {})
        ;
    }

    public static function class(): string
    {
        return ContactPerson::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'details' => PersonFactory::findByEmailOrPhoneOrCreate(
                self::faker()->email(),
                self::faker()->phoneNumber()
            ),
            'relationship' => self::faker()->randomElement([
                'Father',
                'Mother',
                'Sister',
                'Brother',
                'Aunt',
                'Uncle',
                'GrandParent',
            ]),
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
