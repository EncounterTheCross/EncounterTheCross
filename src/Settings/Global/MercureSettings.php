<?php

namespace App\Settings\Global;

class MercureSettings
{
    private bool $active = false;
    private ?string $applicationUrl = null;
    private ?string $publisherUrl = null;

    public function isActive(): bool
    {
        if (null === $this->applicationUrl || null === $this->publisherUrl) {
            return false;
        }

        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}