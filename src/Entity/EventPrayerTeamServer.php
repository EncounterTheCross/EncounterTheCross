<?php

namespace App\Entity;

use App\Entity\Traits\CoreEntityTrait;
use App\Repository\EventPrayerTeamServerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventPrayerTeamServerRepository::class)]
class EventPrayerTeamServer
{
    use CoreEntityTrait;

    public function __toString(): string
    {
        $name = $this->EventParticipant->getPerson()->getFullName();

        if (null !== $this->getPrayerTeam()) {
            $name .= ' on '.$this->getPrayerTeam()->getName();
        }

        return $name;
    }
    #[ORM\ManyToOne(inversedBy: 'prayerTeamServers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'eventPrayerTeamServers')]
    #[ORM\JoinColumn(nullable: true, )]
    private ?PrayerTeam $PrayerTeam = null;

    #[ORM\ManyToOne(inversedBy: 'eventPrayerTeamServers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventParticipant $EventParticipant = null;

    #[ORM\Column]
    private bool $checkedIn = false;

    #[ORM\ManyToOne(inversedBy: 'leaderEventPrayerTeamServers')]
    private ?PrayerTeam $intersessionAssignment = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getPrayerTeam(): ?PrayerTeam
    {
        return $this->PrayerTeam;
    }

    public function setPrayerTeam(?PrayerTeam $PrayerTeam): static
    {
        $this->PrayerTeam = $PrayerTeam;

        return $this;
    }

    public function getEventParticipant(): ?EventParticipant
    {
        return $this->EventParticipant;
    }

    public function setEventParticipant(?EventParticipant $EventParticipant): static
    {
        $this->EventParticipant = $EventParticipant;

        return $this;
    }

    public function isCheckedIn(): ?bool
    {
        return $this->checkedIn;
    }

    public function setCheckedIn(bool $checkedIn): static
    {
        $this->checkedIn = $checkedIn;

        return $this;
    }

    public function getIntersessionAssignment(): ?PrayerTeam
    {
        return $this->intersessionAssignment;
    }

    public function setIntersessionAssignment(?PrayerTeam $intersessionAssignment): static
    {
        $this->intersessionAssignment = $intersessionAssignment;

        return $this;
    }
}
