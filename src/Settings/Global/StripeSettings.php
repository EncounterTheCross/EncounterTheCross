<?php

namespace App\Settings\Global;

use App\Enum\SystemModeEnum;
use Tzunghaor\SettingsBundle\Attribute\Setting;

class StripeSettings
{
    #[Setting]
    private string $publicKey = '';

    #[Setting]
    private string $secretKey = '';

    #[Setting]
    private bool $enabled = false;

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function isEnabled(): bool
    {
        if (empty($this->publicKey) || empty($this->secretKey)) {
            return false;
        }
        
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}