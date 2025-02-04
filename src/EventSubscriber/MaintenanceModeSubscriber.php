<?php

namespace App\EventSubscriber;

use App\Settings\Global\SystemSettings;
use stdClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Tzunghaor\SettingsBundle\Service\SettingsService;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    private SystemSettings|stdClass $settings;

    public function __construct(
        private readonly string $environment,
        SettingsService $globalSettings,
        private readonly Environment $twig,
    ) {
        $this->settings = $globalSettings->getSection(SystemSettings::class);
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ('dev' === $this->environment) {
            // We are in development environment, no need to lock things down.
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($this->settings->isMaintenanceMode() && !IpUtils::isPrivateIp($request->getClientIp())) {
            $content = $this->twig->render('maintenance.html.twig');
            $event->setResponse(new Response($content, 503));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
