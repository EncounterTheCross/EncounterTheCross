<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tzunghaor\SettingsBundle\Service\SettingsService;
use App\Settings\Global\RegistrationSettings;

#[Route(
    '/{site}',
    defaults: ['site' => 'men'],
    requirements: ['site' => 'men'],
)]
class MainController extends AbstractController
{
    #[Route(
        '/',
        name: 'app_index',
    )]
    public function index(
        EventRepository $eventRepository,
    ): Response {
        $nextEvent = $eventRepository->findUpcomingEvent();

        $waitlistEnabled = $this->getRegistrationSettings()->isWaitlistEnabled()
            && !$nextEvent?->isRegistrationOpen()
        ;

        return $this->render('frontend/index.html.twig', [
            'waitlist_enabled' => $waitlistEnabled,
            'event' => $nextEvent,
        ]);
    }

    #[Route('/contact', name: 'app_launchpoints')]
    public function contactLaunchPoints(LocationRepository $launchPointRespository)
    {
        // TODO: convert into Twig Component
        $launchPoints = $launchPointRespository->getAllActiveLaunchPoints(['name' => 'asc']);

        $pins = [];
        foreach ($launchPoints as $launchPoint) {
            if ($launchPoint->hasMappingLocation()) {
                $pins[] = [
                    'lat' => $launchPoint->getLatitude(),
                    'lon' => $launchPoint->getLongitude(),
                    'name' => $launchPoint->getName(),
                    'color' => $launchPoint->getPinColor(),
                ];
            }
        }

        return $this->render('frontend/launchpoints.html.twig', [
            'launch_points' => $launchPoints,
            'map_pins' => $pins,
        ]);
    }

    #[Route('/map/pin/{hex}')]
    public function mapIcons(string $hex)
    {
        return new Response('<svg
        width="35" height="35"
    version="1.1"
    id="ETC_MP_'.$hex.'"
    xmlns="http://www.w3.org/2000/svg"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    viewBox="0 0 293.334 293.334"
    xml:space="preserve"
    fill="none"
    stroke="none"
>
    <path style="fill:#'.$hex.';"
          d="M146.667,0C94.903,0,52.946,41.957,52.946,93.721c0,22.322,7.849,42.789,20.891,58.878 c4.204,5.178,11.237,13.331,14.903,18.906c21.109,32.069,48.19,78.643,56.082,116.864c1.354,6.527,2.986,6.641,4.743,0.212 c5.629-20.609,20.228-65.639,50.377-112.757c3.595-5.619,10.884-13.483,15.409-18.379c6.554-7.098,12.009-15.224,16.154-24.084 c5.651-12.086,8.882-25.466,8.882-39.629C240.387,41.962,198.43,0,146.667,0z M146.667,144.358 c-28.892,0-52.313-23.421-52.313-52.313c0-28.887,23.421-52.307,52.313-52.307s52.313,23.421,52.313,52.307 C198.98,120.938,175.559,144.358,146.667,144.358z">
    </path>
    <circle
            style="fill:#'.$hex.';"
            cx="146.667"
            cy="90.196"
            r="21.756">
    </circle>
</svg>', 200, ['Content-Type' => 'image/svg+xml']);
    }

    #[Route('/testimonies', name: 'app_testimonies')]
    public function testimonies()
    {
        //        $queryBuilder = $testimonialRepository->findAllTestimoniesQueryBuilder();
        //        $adaptor = new QueryAdapter($queryBuilder);
        //        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
        //            $adaptor,
        //            $request->query->get('page', 1),
        //            9
        //        );

        return $this->render('frontend/testimonies.html.twig', [
            //            'pager' => $pagerfanta,
        ]);
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

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'tzunghaor_settings.settings_service.global' => '?'.SettingsService::class,
            ]
        );
    }
}
