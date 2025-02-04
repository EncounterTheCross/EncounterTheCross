<?php

namespace App\Factory;

use App\Entity\Person;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

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
