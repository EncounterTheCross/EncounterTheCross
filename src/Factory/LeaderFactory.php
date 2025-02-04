<?php

namespace App\Factory;

use App\Entity\Leader;
use App\Repository\LeaderRepository;
use App\Service\RoleManager\Role;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Leader>
 *
 * @method        Leader|Proxy                     create(array|callable $attributes = [])
 * @method static Leader|Proxy                     createOne(array $attributes = [])
 * @method static Leader|Proxy                     find(object|array|mixed $criteria)
 * @method static Leader|Proxy                     findOrCreate(array $attributes)
 * @method static Leader|Proxy                     first(string $sortedField = 'id')
 * @method static Leader|Proxy                     last(string $sortedField = 'id')
 * @method static Leader|Proxy                     random(array $attributes = [])
 * @method static Leader|Proxy                     randomOrCreate(array $attributes = [])
 * @method static LeaderRepository|RepositoryProxy repository()
 * @method static Leader[]|Proxy[]                 all()
 * @method static Leader[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Leader[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Leader[]|Proxy[]                 findBy(array $attributes)
 * @method static Leader[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Leader[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
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
    protected function initialize(): self
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
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
