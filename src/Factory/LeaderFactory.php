<?php

namespace App\Factory;

use App\Entity\Leader;
use App\Service\RoleManager\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class LeaderFactory extends PersistentProxyObjectFactory
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Leader $leader): void {})
            ->afterInstantiate(function (Leader $leader) {
                if ($leader->getPlainPassword()) {
                    $leader->setPassword(
                        $this->passwordHasher->hashPassword($leader, $leader->getPlainPassword())
                    );
                }
            })
        ;
    }

    public static function class(): string
    {
        return Leader::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'person' => PersonFactory::new(),
            'createdAt' => self::faker()->dateTime(),
            'email' => self::faker()->email(),
            //            'password' => self::faker()->text(),
            'plainPassword' => 'tada',
            'roles' => [Role::LIMITED_FULL],

            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
