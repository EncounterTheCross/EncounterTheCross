<?php

namespace App\Settings\Global;

use Tzunghaor\SettingsBundle\Attribute\Setting;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationSettings
{
    #[Setting]
    private bool $waitlistEnabled = false;

    #[Setting(formType: TextareaType::class)]
    private string $launchPointDescriptionText = '';

    public function isWaitlistEnabled(): bool
    {
        return $this->waitlistEnabled;
    }

    public function setWaitlistEnabled(bool $waitlistEnabled): void
    {
        $this->waitlistEnabled = $waitlistEnabled;
    }

    public function getLaunchPointDescriptionText(): string
    {
        return $this->launchPointDescriptionText;
    }

    public function setLaunchPointDescriptionText(string $launchPointDescriptionText): void
    {
        $this->launchPointDescriptionText = $launchPointDescriptionText;
    }
    
    /*
     * TODO: Add registration related settings here, from global settings
    */
    // #[Setting]
    // private bool $registrationDeadlineInforced = false;

    // public function isRegistrationDeadlineInforced(): bool
    // {
    //     return $this->registrationDeadlineInforced;
    // }

    // public function setRegistrationDeadlineInforced(bool $registrationDeadlineInforced): void
    // {
    //     $this->registrationDeadlineInforced = $registrationDeadlineInforced;
    // }
}
