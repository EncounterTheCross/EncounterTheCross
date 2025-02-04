<?php

namespace App\Factory;

use App\Entity\ContactPerson;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

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
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
