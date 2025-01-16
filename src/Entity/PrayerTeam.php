<?php

namespace App\Entity;

use App\Entity\Traits\CoreEntityTrait;
use App\Repository\PrayerTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrayerTeamRepository::class)]
class PrayerTeam
{
    use CoreEntityTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, EventPrayerTeamServer>
     */
    #[ORM\OneToMany(mappedBy: 'PrayerTeam', targetEntity: EventPrayerTeamServer::class)]
    private Collection $eventPrayerTeamServers;

    /**
     * @var Collection<int, EventPrayerTeamServer>
     */
    #[ORM\OneToMany(mappedBy: 'intersessionAssignment', targetEntity: EventPrayerTeamServer::class)]
    private Collection $leaderEventPrayerTeamServers;

    #[ORM\Column]
    private bool $requiresIntersession = false;

    public function __construct()
    {
        $this->eventPrayerTeamServers = new ArrayCollection();
        $this->leaderEventPrayerTeamServers = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, EventPrayerTeamServer>
     */
    public function getEventPrayerTeamServers(): Collection
    {
        return $this->eventPrayerTeamServers;
    }

    public function addEventPrayerTeamServer(EventPrayerTeamServer $eventPrayerTeamServer): static
    {
        if (!$this->eventPrayerTeamServers->contains($eventPrayerTeamServer)) {
            $this->eventPrayerTeamServers->add($eventPrayerTeamServer);
            $eventPrayerTeamServer->setPrayerTeam($this);
        }

        return $this;
    }

    public function removeEventPrayerTeamServer(EventPrayerTeamServer $eventPrayerTeamServer): static
    {
        if ($this->eventPrayerTeamServers->removeElement($eventPrayerTeamServer)) {
            // set the owning side to null (unless already changed)
            if ($eventPrayerTeamServer->getPrayerTeam() === $this) {
                $eventPrayerTeamServer->setPrayerTeam(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    /**
     * @return Collection<int, EventPrayerTeamServer>
     */
    public function getLeaderEventPrayerTeamServers(): Collection
    {
        return $this->leaderEventPrayerTeamServers;
    }

    public function addLeaderEventPrayerTeamServer(EventPrayerTeamServer $leaderEventPrayerTeamServer): static
    {
        if (!$this->leaderEventPrayerTeamServers->contains($leaderEventPrayerTeamServer)) {
            $this->leaderEventPrayerTeamServers->add($leaderEventPrayerTeamServer);
            $leaderEventPrayerTeamServer->setIntersessionAssignment($this);
        }

        return $this;
    }

    public function removeLeaderEventPrayerTeamServer(EventPrayerTeamServer $leaderEventPrayerTeamServer): static
    {
        if ($this->leaderEventPrayerTeamServers->removeElement($leaderEventPrayerTeamServer)) {
            // set the owning side to null (unless already changed)
            if ($leaderEventPrayerTeamServer->getIntersessionAssignment() === $this) {
                $leaderEventPrayerTeamServer->setIntersessionAssignment(null);
            }
        }

        return $this;
    }

    public function isRequiresIntersession(): ?bool
    {
        return $this->requiresIntersession;
    }

    public function setRequiresIntersession(bool $requiresIntersession): static
    {
        $this->requiresIntersession = $requiresIntersession;

        return $this;
    }
}
