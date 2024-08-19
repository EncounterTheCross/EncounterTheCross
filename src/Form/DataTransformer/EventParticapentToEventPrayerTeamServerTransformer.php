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
            return '';
        }

        if (!$value instanceof EventParticipant) {
            throw new LogicException(sprintf('The %s can only be used with %s objects', ServerPrayerTeamAssignmentEmbeddedFormType::class, EventParticipant::class));
        }

        $prayerAssignment = new EventPrayerTeamServer();
        $prayerAssignment->setEvent($value->getEvent());
        $prayerAssignment->setEventParticipant($value);

        return $prayerAssignment;
    }

    public function reverseTransform(mixed $value)
    {
        dd('reverse transform', $value);
    }
}
