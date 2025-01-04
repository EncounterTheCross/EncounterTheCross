<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/13/24
 * Project: EncounterTheCross
 * File Name: RamChecker.php
 */

namespace App\Check;

use App\Command\Traits\TerminalFreeCli;
use App\Command\Traits\TerminalTopCli;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Psr\Log\LoggerInterface;

class TopChecker implements CheckInterface
{
    use TerminalTopCli;
    use TerminalFreeCli;

    public function __construct(
        private readonly LoggerInterface $healthCheckLogger,
    ) {
    }

    public function check(): Success|Warning
    {
        $output = null;
        // Get the output and split into lines
        //        if ($this->hasFreeTerminalCommand() && null === $output) {
        //            $this->commandRunner = TerminalFreeCli::class;
        //            $output = $this->getFreeTerminalRunOutput();
        //        }
        if ($this->hasTopTerminalCommand() && null === $output) {
            $output = $this->getTopTerminalRunOutput();
        }

        if (false === $output || !is_string($output)) {
            return new Warning(
                'No terminal command found that can check the RAM. 
                [Windows Systems do not have a way to run (uname, which, top, free), 
                meaning we can not change the command that is ran for 
                its Operating System.] You can create an alias to those 
                commands and then the script will try and run it.'
            );
        }

        $lines = explode("\n", $output);

        $data = [];
        $headers = true;
        foreach ($lines as $line) {
            // Parse each line (this is a simplified example)
            // You need to implement your own logic to parse the output correctly
            if (empty($line)) {
                $headers = false;
            }
            if ($headers) {
                $data[] = $line;
            }
        }

        // Check Values HERE
        // Using this more as a logger than a check.
        $this->healthCheckLogger->info('TOP Command Information', $data);

        // if we have made it this far we are successful
        return new Success();
    }

    public function getLabel(): string
    {
        return 'Server TOP data log';
    }
}
