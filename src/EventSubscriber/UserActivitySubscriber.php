<?php

namespace App\EventSubscriber;

use App\Entity\Leader;
use App\Entity\UserActivity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserActivitySubscriber implements EventSubscriberInterface
{
    private ?UserActivity $currentActivity = null;
    private array $config = [
        'excluded_routes' => [
            'app_server_checkin_register',
            'ux_live_component',
        ],
        'excluded_paths' => [],
        'excluded_ips' => [],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
//        BrowserDetectorService $browserDetector,
//        GeoLocationService $geoLocationService,
        array $config = []
    ) {
//        $this->entityManager = $entityManager;
//        $this->sercurity = $security;
//        $this->requestStack = $requestStack;
//        $this->browserDetector = $browserDetector;
//        $this->geoLocationService = $geoLocationService;
        $this->config = array_merge_recursive($this->config, $config);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 7], // Lower priority than firewall
            KernelEvents::RESPONSE => ['onKernelResponse', -15], // Run after most response listeners
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Skip tracking for excluded routes or paths
        if ($this->shouldSkipTracking($request)) {
            return;
        }

        // Create new activity record
        $this->currentActivity = new UserActivity();
        $this->currentActivity->setRequestTime(new \DateTime());

        // Set request data
        $this->currentActivity->setIpAddress($request->getClientIp());
        $this->currentActivity->setRequestMethod($request->getMethod());
        $this->currentActivity->setRequestUri($request->getRequestUri());
        $this->currentActivity->setRoute($request->attributes->get('_route'));

        // Set user agent info
        $userAgent = $request->headers->get('User-Agent');
        $this->currentActivity->setUserAgent($userAgent);

        // Use browser detector service to get device and browser info
//        $browserInfo = $this->browserDetector->detect($userAgent);
//        $this->currentActivity->setBrowser($browserInfo['browser'] ?? null);
//        $this->currentActivity->setBrowserVersion($browserInfo['version'] ?? null);
//        $this->currentActivity->setOperatingSystem($browserInfo['platform'] ?? null);
//        $this->currentActivity->setDeviceType($browserInfo['device'] ?? null);
//
        // Set user info if authenticated
        $token = $this->security->getToken();

        if ($token && $token->getUser() && is_object($token->getUser())) {
            /** @var Leader $user */
            $user = $token->getUser();
            $this->currentActivity->setUserId($user->getId());
            $this->currentActivity->setUsername($user->getUserIdentifier());
        }

        // Set session ID
        $session = $request->getSession();
        if ($session && $session->isStarted()) {
            $this->currentActivity->setSessionId($session->getId());
        }

        // Set referrer if available
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            $this->currentActivity->setReferrer($referrer);
        }

//        // Set geolocation data if enabled
//        if ($this->config['geo_tracking_enabled'] ?? true) {
//            $ipAddress = $request->getClientIp();
//            $geoData = $this->geoLocationService->locate($ipAddress);
//
//            if ($geoData) {
//                $this->currentActivity->setCountry($geoData['country'] ?? null);
//                $this->currentActivity->setCity($geoData['city'] ?? null);
//                $this->currentActivity->setLatitude($geoData['latitude'] ?? null);
//                $this->currentActivity->setLongitude($geoData['longitude'] ?? null);
//            }
//        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->currentActivity) {
            return;
        }

        $request = $event->getRequest();

        // Skip tracking for excluded routes or paths
        if ($this->shouldSkipTracking($request)) {
            return;
        }

        $response = $event->getResponse();

        // Set response data
        $this->currentActivity->setResponseTime(new \DateTime());
        $this->currentActivity->setStatusCode($response->getStatusCode());
        $this->currentActivity->setContentType($response->headers->get('Content-Type'));

        // Calculate duration in milliseconds
        $requestTime = $this->currentActivity->getRequestTime();
        $responseTime = $this->currentActivity->getResponseTime();
        $duration = ($responseTime->getTimestamp() - $requestTime->getTimestamp()) * 1000;
        $duration += ($responseTime->format('u') - $requestTime->format('u')) / 1000;
        $this->currentActivity->setDuration((int) $duration);

        // Persist the activity record
        $this->entityManager->persist($this->currentActivity);
        $this->entityManager->flush();

        // Reset current activity
        $this->currentActivity = null;
    }

    private function shouldSkipTracking($request): bool
    {
        // Skip tracking for excluded routes
        $route = $request->attributes->get('_route');
        if (str_starts_with($route, '_')) {
            return true;
        }

        if (in_array($route, $this->config['excluded_routes'] ?? [])) {
            return true;
        }

        // Skip tracking for excluded paths
        $path = $request->getPathInfo();
        foreach ($this->config['excluded_paths'] ?? [] as $excludedPath) {
            if (preg_match($excludedPath, $path)) {
                return true;
            }
        }

        // Skip tracking for asset requests
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }

        // Skip tracking for excluded IPs
        $clientIp = $request->getClientIp();
        if (in_array($clientIp, $this->config['excluded_ips'] ?? [])) {
            return true;
        }

        return false;
    }
}
