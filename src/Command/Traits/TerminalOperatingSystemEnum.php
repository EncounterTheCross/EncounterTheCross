<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/13/24
 * Project: EncounterTheCross
 * File Name: TerminalOperatingSystemEnum.php
 */

namespace App\Command\Traits;

enum TerminalOperatingSystemEnum
{
    public const LINUX_NAME = 'Linux';
    public const MACOS_NAME = 'Darwin';
    public const WINDOWS_NAME = '';

    case LINUX;
    case MACOS;
    case WINDOWS;

    public function isLinux(): bool
    {
        return TerminalOperatingSystemEnum::LINUX === $this;
    }

    public function isMacOS(): bool
    {
        return TerminalOperatingSystemEnum::MACOS === $this;
    }

    public function isWindows(): bool
    {
        return TerminalOperatingSystemEnum::WINDOWS === $this;
    }
}
