<?php

namespace App\Factory;

use App\Entity\Leader;
use App\Repository\LeaderRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Leader>
 *
 * @method        Leader|Proxy create(array|callable $attributes = [])
 * @method static Leader|Proxy createOne(array $attributes = [])
 * @method static Leader|Proxy find(object|array|mixed $criteria)
 * @method static Leader|Proxy findOrCreate(array $attributes)
 * @method static Leader|Proxy first(string $sortedField = 'id')
 * @method static Leader|Proxy last(string $sortedField = 'id')
 * @method static Leader|Proxy random(array $attributes = [])
 * @method static Leader|Proxy randomOrCreate(array $attributes = [])
 * @method static LeaderRepository|RepositoryProxy repository()
 * @method static Leader[]|Proxy[] all()
 * @method static Leader[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Leader[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Leader[]|Proxy[] findBy(array $attributes)
 * @method static Leader[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Leader[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class LeaderFactory extends ModelFactory
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
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * TODO remove row pointer once DoctrineEvent Hook is used
     */
    protected function getDefaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'email' => self::faker()->email(),
//            'password' => self::faker()->text(),
            'plainPassword' => 'tada',
            'roles' => ['ROLE_ADMIN'],
            'rowPointer' => new Uuid(self::faker()->uuid()),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {

        return $this
            // ->afterInstantiate(function(Leader $leader): void {})
            ->afterInstantiate(function(Leader $leader) {
                if ($leader->getPlainPassword()) {
                    $leader->setPassword(
                        $this->passwordHasher->hashPassword($leader, $leader->getPlainPassword())
                    );
                }
            })
        ;
    }

    protected static function getClass(): string
    {
        return Leader::class;
    }
}
