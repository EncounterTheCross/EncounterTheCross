<?php

namespace App\Form\DataTransformer;

use App\Entity\EventParticipant;
use App\Entity\EventPrayerTeamServer;
use App\Form\ServerPrayerTeamAssignmentEmbeddedFormType;
use LogicException;
use Symfony\Component\Form\DataTransformerInterface;

class EventParticapentToEventPrayerTeamServerTransformer implements DataTransformerInterface
{
    public function transform(mixed $value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof EventParticipant) {
            throw new LogicException(sprintf('The %s can only be used with %s objects', ServerPrayerTeamAssignmentEmbeddedFormType::class, EventParticipant::class));
        }

        $prayerAssignment = new EventPrayerTeamServer();
        //        $event = $value->getEvent();

        //        $prayerAssignment->setEvent($event);
        $prayerAssignment->setEventParticipant($value);

        $value->addEventPrayerTeamServer($prayerAssignment);

        return $prayerAssignment;
    }

    public function reverseTransform(mixed $value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof EventPrayerTeamServer) {
            throw new LogicException('Expected EventPrayerTeamServer object');
        }

        $participant = $value->getEventParticipant();
        if (!$participant) {
            throw new LogicException('EventParticipant cannot be null');
        }

        // Maintain bidirectional relationship
        $participant->addEventPrayerTeamServer($value);
        //        $value->setEventParticipant($participant);

        return $participant;
    }
}
