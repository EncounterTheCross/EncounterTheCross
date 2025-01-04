<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/13/24
 * Project: EncounterTheCross
 * File Name: TerminalUnameCli.php
 */

namespace App\Command\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait TerminalUnameCli
{
    private function hasTerminalCommand(): bool
    {
        // Create a process to run 'which top'
        $process = new Process(['which', 'uname']);
        $process->run();

        // Check the output
        if ($process->getOutput()) {
            return true;
        }

        return false;
    }

    private function getTerminalRunOutput(): TerminalOperatingSystemEnum|false
    {
        if (!$this->hasTerminalCommand()) {
            return false;
        }

        $process = new Process(['uname']);
        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        /*
         * this is expected to return with "\n" at the end, and is only 1 line
         * with that we can assume that the "0" index is the answer we want.
         */
        $output = explode("\n", $process->getOutput())[0];

        return match ($output) {
            TerminalOperatingSystemEnum::LINUX_NAME => TerminalOperatingSystemEnum::LINUX,
            TerminalOperatingSystemEnum::MACOS_NAME => TerminalOperatingSystemEnum::MACOS,
            // TODO: currently no way to run in Windows Environment
            //            'Windows' => TerminalOperatingSystemEnum::WINDOWS,
            default => TerminalOperatingSystemEnum::MACOS,
        };
    }
}
