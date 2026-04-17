<?php

namespace App\Settings\Global;

use Symfony\Component\Validator\Constraints as Assert;
use Tzunghaor\SettingsBundle\Attribute\Setting;

class StripeSettings
{
    #[Setting]
    private string $publicKey = '';

    #[Setting]
    private string $secretKey = '';

    #[Setting(
        label: 'Platform Fee Rate',
        help: 'Platform Fee Rate, in decimal percentage value.'
    )]
    #[Assert\Range(min: 0.0, max: 0.9999)]
    private float $feeRate = 0.0;

    #[Setting(
        label: 'Platform Fee Base Rate',
        help: 'Platform Fee Base Rate, in cents.'
    )]
    #[Assert\GreaterThanOrEqual(0)]
    private int $feeBase = 0;

    #[Setting]
    private bool $enabled = false;

    #[Setting]
    private string $webhookSecret = '';

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
        if (empty($this->publicKey) || empty($this->secretKey) || empty($this->webhookSecret)) {
            return false;
        }

        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getFeeRate(): float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): void
    {
        $this->feeRate = $feeRate;
    }

    public function getFeeBase(): int
    {
        return $this->feeBase;
    }

    public function setFeeBase(int $feeBase): void
    {
        $this->feeBase = $feeBase;
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    public function setWebhookSecret(string $webhookSecret): self
    {
        $this->webhookSecret = $webhookSecret;

        return $this;
    }
}
