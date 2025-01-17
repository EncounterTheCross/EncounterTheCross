<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:opcache:reset',
    description: 'Resets the OPcache',
)]
class OpcacheClearCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!function_exists('opcache_reset')) {
            $io->error('OPcache is not enabled on this server');

            return Command::FAILURE;
        }

        if (opcache_reset()) {
            $io->success('OPcache successfully reset');

            return Command::SUCCESS;
        }

        $io->error('Failed to reset OPcache, or OPcache is not enabled on this server');

        return Command::FAILURE;
    }
}
