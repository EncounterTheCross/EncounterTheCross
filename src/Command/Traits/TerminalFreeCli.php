<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/13/24
 * Project: EncounterTheCross
 * File Name: TerminalFreeCli.php
 */

namespace App\Command\Traits;

use Symfony\Component\Process\Process;

trait TerminalFreeCli
{
    private function hasFreeTerminalCommand(): bool
    {
        // Create a process to run 'which top'
        $process = new Process(['which', 'free']);
        $process->run();

        // Check the output
        if ($process->getOutput()) {
            return true;
        }

        return false;
    }

    private function getFreeTerminalRunOutput(): string|false
    {
        // Create a process to run 'which top'
        $process = new Process(['free']);
        $process->run();

        // Check the output
        $output = $process->getOutput();
        if ($output) {
            return $output;
        }

        return false;
    }
}
