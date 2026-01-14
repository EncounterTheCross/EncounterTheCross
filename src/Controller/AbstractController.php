<?php

namespace App\Controller;

use App\Settings\Global\MercureSettings;
use App\Settings\Global\SystemSettings;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as CoreAbstractController;
use Tzunghaor\SettingsBundle\Service\SettingsService;
use App\Settings\Global\RegistrationSettings;

class AbstractController extends CoreAbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'tzunghaor_settings.settings_service.global' => '?'.SettingsService::class,
            ]
        );
    }

    protected function getGlobalSettings(): SystemSettings
    {
        return $this->getSettingsService()->getSection(SystemSettings::class);
    }

    protected function getMercureSettings(): MercureSettings
    {
        return $this->getSettingsService()->getSection(MercureSettings::class);
    }

    protected function getRegistrationSettings(): RegistrationSettings
    {
        return $this->getSettingsService()->getSection(RegistrationSettings::class);
    }

    private function getSettingsService(): SettingsService
    {
        if (!$this->container->has('tzunghaor_settings.settings_service.global')) {
            throw new LogicException('The SettingsBundle is not registered in your application. Try running "composer require tzunghaor/settings-bundle".');
        }

        return $this->container->get('tzunghaor_settings.settings_service.global');
    }
}
