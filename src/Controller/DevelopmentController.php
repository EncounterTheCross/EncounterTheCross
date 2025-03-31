<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class DevelopmentController extends AbstractController
{
    #[Route('/mercure-test', name: 'app_mercure_test', env: 'dev',)]
    public function index(): Response
    {
        return $this->render('tests/mercure.html.twig', [
            'mercure_public_url' => $_ENV['MERCURE_PUBLIC_URL'] ?? 'https://mercure.encounterthecross.test/.well-known/mercure',
        ]);
    }

    #[Route('/mercure-publish', name: 'app_mercure_publish', env: 'dev',)]
    public function publish(HubInterface $hub): Response
    {
        $update = new Update(
            'https://encounterthecross.test/messages',
            json_encode([
                'message' => 'Hello from ' . $_SERVER['HTTP_HOST'] . ' at ' . date('H:i:s'),
                'from' => $_SERVER['HTTP_HOST'],
            ])
        );

        $hub->publish($update);

        return $this->json([
            'status' => 'published',
            'message' => 'Message published from ' . $_SERVER['HTTP_HOST'],
        ]);
    }
}
