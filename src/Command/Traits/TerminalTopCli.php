<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/13/24
 * Project: EncounterTheCross
 * File Name: TerminalTopCli.php
 */

namespace App\Command\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait TerminalTopCli
{
    use TerminalUnameCli {
        getTerminalRunOutput as getUnameTerminalCommand;
    }

    private ?TerminalOperatingSystemEnum $operatingSystemEnum = null;

    private function hasTopTerminalCommand(): bool
    {
        $operatingSystem = $this->getUnameTerminalCommand();
        if ($operatingSystem) {
            $this->operatingSystemEnum = $operatingSystem;

            return true;
        }

        return false;
    }

    private function getTopTerminalRunOutput(): string|false
    {
        if (!$this->hasTerminalCommand()) {
            return false;
        }

        $process = match (true) {
            // No Windows option right now
            $this->operatingSystemEnum->isLinux() => new Process(['top', '-b', '-n', '1']),
            $this->operatingSystemEnum->isMacOS() => new Process(['top', '-l', '1', '-n', '1']),
            default => throw new ProcessFailedException(new Process(['top'])),
        };
        // Make time out 30 seconds.
        $process->setIdleTimeout(30);
        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Get the output and split into lines
        return $process->getOutput();
    }
}
