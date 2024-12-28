<?php

namespace App\Controller\Admin\Crud\Extended;

use App\Entity\Event;
use App\Repository\EventRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use LogicException;

trait HasPrayerTeamAssignmentActionTrait
{
    public function assignPrayerTeams(AdminContext $adminContext, EventRepository $eventRepository)
    {
        $parentEvent = $this->getAdminUrlGenerator()->get(ParentCrudControllerInterface::PARENT_ID);
        $event = $adminContext->getEntity()->getInstance();
        if (null !== $parentEvent) {
            $event = $eventRepository->find($parentEvent);
        }

        if (!$event instanceof Event) {
            throw new LogicException('Entity is missing or not an Event');
        }

        return $this->redirect(
            $this->getAdminUrlGenerator()->setRoute('event_prayer_team_assignments', [
                'event' => $event->getId(),
            ])->generateUrl()
        );
    }
}
