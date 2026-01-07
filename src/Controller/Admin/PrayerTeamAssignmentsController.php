<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Controller\Admin\Crud\EventPrayerTeamServerCrudController;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Entity\Leader;
use App\Entity\Location;
use App\Enum\EventParticipantStatusEnum;
use App\Form\LaunchPointServerPrayerTeamAssignmentsType;
use App\Repository\EventRepository;
use App\Repository\PrayerTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(
    path: '/admin/encounter/prayer_team_assignments',
)]
class PrayerTeamAssignmentsController extends AbstractController
{
    #[Route(
        path: '/launches',
        name: 'event_prayer_team_assignments',
    )]
    public function index(EventRepository $eventRepository, Request $request, AdminUrlGenerator $adminUrlGenerator)
    {
        if (!$this->isGranted('ROLE_LEADER')) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $eventId = $request->query->all('routeParams')['event'] ?? null;
        $event = match (true) {
            null !== $eventId => $eventRepository->find($eventId),
            default => $eventRepository->findUpcomingEvent(),
        };

        $launchPoints = $event->getLaunchPoints();
        if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE')) {
            /** @var Leader $user */
            $user = $this->getUser();
            $launchPoint = $launchPoints->filter(function (Location $location) use ($user) {
                return $location->getId() === $user->getLaunchPoint()->getId();
            })->first();

            return $this->redirect(
                $adminUrlGenerator->setRoute(
                    'event_launch_prayer_team_assignments',
                    [
                        'location' => $launchPoint->getId(),
                        'event' => $event->getId(),
                    ]
                )
                    ->generateUrl()
            );
        }

        return $this->render('admin/crud/PrayerTeamAssignments/launches.html.twig', [
            'launchPoints' => $launchPoints,
            'eventId' => $event->getId(),
        ]);
    }

    #[Route(
        path: '/{event}/launches/{location}',
        name: 'event_launch_prayer_team_assignments',
    )]
    public function prayerTeamAssignments(EntityManagerInterface $entityManager, Request $request, Location $location, ?Event $event = null)
    {
        /** @var Leader $leader */
        $leader = $this->getUser();

        if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE') && $leader->getLaunchPoint()->getId() !== $location->getId()) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $servers = $event->getEventParticipants()->filter(function (EventParticipant $participant) use ($location) {
            if ($participant->isAttendee()) {
                return false;
            }

            if ($participant->getLaunchPoint() !== $location) {
                return false;
            }

            // Validate the attending status

            if ($participant->getStatus() !== EventParticipantStatusEnum::ATTENDING->value) {
                return false;
            }

            return true;
        });

        $servers->map(function (EventParticipant $server) {
            if (0 === $server->getEventPrayerTeamServers()->count()) {
                $assignment = new EventPrayerTeamServer();
                $server->addEventPrayerTeamServer($assignment);
            }
        });

        $form = $this->createForm(LaunchPointServerPrayerTeamAssignmentsType::class, ['eventParticipants' => $servers]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData()['eventParticipants'] as $server) {
                $entityManager->persist($server);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Assignments Added');

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/crud/PrayerTeamAssignments/server-assignments.html.twig', [
            'form' => $form->createView(),
            'launchPoint' => $location,
        ]);
    }

    #[Route(
        path: '/{event}',
        name: 'event_launch_prayer_team_assignments_report',
    )]
    public function prayerTeamAssignmentsReport(Event $event, AdminUrlGenerator $adminUrlGenerator, PrayerTeamRepository $prayerTeamRepository)
    {
        if (!$this->isGranted('ROLE_LEADER')) {
            throw new AccessDeniedException('You do not have permission to assign prayer teams for launch points.');
        }

        $criteria = Criteria::create()
            ->orderBy([
                'person.firstName' => Order::Ascending,
            ]);

        /** @var ArrayCollection $servers */
        $servers = $event->getEventParticipants(EventParticipantStatusEnum::ATTENDING)->filter(function (EventParticipant $participant) {
            return $participant->isServer();
        });
        $servers = $servers->matching($criteria);

        $teams = [
            'unassigned' => [],
        ];

        $servers->map(function (EventParticipant $server) use (&$teams) {
            $assignment = $server->getCurrentEventPrayerTeamServer();
            $teamId = 'unassigned';
            if (null !== $assignment) {
                $teamId = $assignment->getPrayerTeam()?->getName();

                if($teamId !== null) {
                    return;
                }

                if (!array_key_exists($teamId, $teams)) {
                    $teams[$teamId] = [];
                }
            }

            $teams[$teamId][] = $server;
        });

        ksort($teams);

        $prayerTeam = $prayerTeamRepository->findOneBy([
            'requiresIntersession' => true,
        ]);

        if (null !== $prayerTeam) {
            // actions
            $leaderCrudLink = $adminUrlGenerator
                ->setController(EventPrayerTeamServerCrudController::class)
                ->setAction(Crud::PAGE_INDEX)
                ->set('event', $event->getId())
                ->set('filters', [
                    'PrayerTeam' => [
                        'comparison' => '=',
                        'value' => $prayerTeam->getId(),
                    ],
                    'event' => [
                        'comparison' => '=',
                        'value' => $event->getId(),
                    ],
                ])
            ;
            $action = Action::new('assign_leadership_intersessions')
                ->linkToUrl($leaderCrudLink)
//            ->displayAsButton()
                ->setIcon('fa fa-pencil')
                ->getAsDto();
            $action->setLinkUrl($leaderCrudLink); // hmm this is weird but needed
        }





        return $this->render('admin/page/server_assignment_report.html.twig', [
            'teams' => $teams,
            'action' => $action ?? null,
        ]);
    }
}
