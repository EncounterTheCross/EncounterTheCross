<?php

namespace App\Settings\Global;

class MercureSettings
{
    private bool $active = false;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}