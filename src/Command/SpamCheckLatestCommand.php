<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\SpamDetection\SpamDetectionService;
use App\Repository\EventRepository;
use App\Repository\EventParticipantRepository;

#[AsCommand(
    name: 'app:spam-check:latest',
    description: 'check the latest event registrations for spam',
)]
class SpamCheckLatestCommand extends Command
{
    public function __construct(
        EventParticipantRepository $eventParticipantRepository,
        SpamDetectionService $spamDetectionService,
        EventRepository $eventRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->eventRepository->findUpcomingEvent()?->getEventParticipants()->forAll(function($key, $participant) use ($io) {
            if ($participant->getRawSpamDetails() !== null) {
                return true; // continue iteration
            }
            
            $spamDetails = $this->spamDetectionService->getSpamDetails(
                $eventRegistration->getSpamSerialization()
            );
            $eventRegistration->setRawSpamDetails(
                $spamDetails
            );

            $this->eventParticipantRepository->save($eventRegistration, true);

            if ($eventRegistration->isSpam()) {
                $io->warning(sprintf('Participant ID %d flagged as spam with score %d', $participant->getId(), $spamScore));
            } 
            return true; // continue iteration
        });

        $io->success('Finished Checking all submissions for Spam');

        return Command::SUCCESS;
    }
}
