<?php

namespace App\Command;

use App\Entity\Leader;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Add a short description for your command',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('userEmail', null, InputOption::VALUE_OPTIONAL, 'The admin users email address to use')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roles = ['ROLE_SUPER_ADMIN'];

        $io = new SymfonyStyle($input, $output);
        $email = $input->getOption('userEmail');

        if (!$email) {
            $email = $io->ask('What is the admins email address?');
        }

        $passwordplain = $io->ask('Whats the password the user will use?');

        $person = new Person();
        $person
            ->setFirstName($io->ask('Admin\'s first name?'))
            ->setLastName($io->ask('Admin\'s last name?'))
            ->setEmail($email)
        ;

        $admin = new Leader();
        $admin
            ->setEmail($email)
//            ->setPassword($password)
            ->setRoles($roles)
            ->setPerson($person)
        ;

        $admin->setPassword($this->passwordHasher->hashPassword($admin, $passwordplain));

        $this->em->persist($person);
        $this->em->persist($admin);
        $this->em->flush();

        $io->success('Admin user has been created!');

        return Command::SUCCESS;
    }
}
