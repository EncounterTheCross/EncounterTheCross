<?php

namespace App\Settings\Global;

use Tzunghaor\SettingsBundle\Attribute\Setting;

class RegistrationSettings
{
    #[Setting]
    private bool $waitlistEnabled = false;

    public function isWaitlistEnabled(): bool
    {
        return $this->waitlistEnabled;
    }

    public function setWaitlistEnabled(bool $waitlistEnabled): void
    {
        $this->waitlistEnabled = $waitlistEnabled;
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
