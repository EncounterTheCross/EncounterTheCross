<?php

namespace App\Entity;

use App\Entity\Traits\CoreEntityTrait;
use App\Enum\EventParticipantStatusEnum;
use App\Exception\InvalidLocationType;
use App\Repository\EventRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use CoreEntityTrait;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $end = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $registrationDeadLineServers = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToMany(targetEntity: Location::class, inversedBy: 'launchPointEvents')]
    #[ORM\OrderBy(['name' => 'asc'])]
    private Collection $launchPoints;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventParticipant::class)]
    private Collection $eventParticipants;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 8)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, EventPrayerTeamServer>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventPrayerTeamServer::class)]
    private Collection $prayerTeamServers;

    #[ORM\Column]
    private ?bool $registrationOpen = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $prayerTeamAssignmentsDeadline = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $checkInToken = null;

    public function __construct()
    {
        $this->launchPoints = new ArrayCollection();
        $this->eventParticipants = new ArrayCollection();
        $this->prayerTeamServers = new ArrayCollection();
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getRegistrationDeadLineServers(): ?DateTimeInterface
    {
        return $this->registrationDeadLineServers;
    }

    public function setRegistrationDeadLineServers(DateTimeInterface $registrationDeadLineServers): self
    {
        $this->registrationDeadLineServers = $registrationDeadLineServers;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        if (Location::TYPE_EVENT !== $location->getType()) {
            throw new InvalidLocationType('Location', Location::TYPE_EVENT);
        }

        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLaunchPoints(): Collection
    {
        return $this->launchPoints;
    }

    public function addLaunchPoint(Location $launchPoint): self
    {
        if (!$this->launchPoints->contains($launchPoint)) {
            $this->launchPoints->add($launchPoint);
        }

        return $this;
    }

    public function removeLaunchPoint(Location $launchPoint): self
    {
        $this->launchPoints->removeElement($launchPoint);

        return $this;
    }

    public function getAllAttending(): Collection
    {
        return $this->getEventParticipants(EventParticipantStatusEnum::ATTENDING);
    }

    public function getDrops(): Collection
    {
        return $this->getEventParticipants(EventParticipantStatusEnum::DROPPED);
    }

    public function getDropTotal(): float
    {
        return $this->getDrops()->count();
    }

    public function getAttendingTotal(): float
    {
        return $this->getAllAttending()->count();
    }

    public function getTotalServers(): int
    {
        $servers = $this->getEventParticipants()->filter(function (EventParticipant $eventParticipant) {
            return (EventParticipant::TYPE_SERVER === $eventParticipant->getType())
                && (EventParticipantStatusEnum::ATTENDING->value === $eventParticipant->getStatus());
        });

        return $servers->count();
    }

    public function getTotalAttendees(): int
    {
        $attendees = $this->getEventParticipants()->filter(function (EventParticipant $eventParticipant) {
            return (EventParticipant::TYPE_ATTENDEE === $eventParticipant->getType())
                && (EventParticipantStatusEnum::ATTENDING->value === $eventParticipant->getStatus());
        });

        return $attendees->count();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return Collection<int, EventParticipant>
     */
    public function getEventParticipants(?EventParticipantStatusEnum $status = null): Collection
    {
        $participants = $this->eventParticipants;

        if ($status) {
            return $participants->filter(function (EventParticipant $participant) use ($status) {
                //                if ($participant->getStatus() !== $status->value) {
                //                    dump($participant->getStatus(), $status->value);
                //                }
                return $participant->getStatus() === $status->value;
            });
        }

        return $participants;
    }

    public function addEventParticipant(EventParticipant $eventParticipant): self
    {
        if (!$this->eventParticipants->contains($eventParticipant)) {
            $this->eventParticipants->add($eventParticipant);
            $eventParticipant->setEvent($this);
        }

        return $this;
    }

    public function removeEventParticipant(EventParticipant $eventParticipant): self
    {
        if ($this->eventParticipants->removeElement($eventParticipant)) {
            // set the owning side to null (unless already changed)
            if ($eventParticipant->getEvent() === $this) {
                $eventParticipant->setEvent(null);
            }
        }

        return $this;
    }

    public function clearEventParticipants(): self
    {
        $this->eventParticipants->clear();

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function canServerRegister(): bool
    {
        return $this->registrationDeadLineServers > new DateTime();
    }

    /**
     * @return Collection<int, EventPrayerTeamServer>
     */
    public function getPrayerTeamServers(): Collection
    {
        return $this->prayerTeamServers;
    }

    public function addPrayerTeamServer(EventPrayerTeamServer $prayerTeamServer): static
    {
        if (!$this->prayerTeamServers->contains($prayerTeamServer)) {
            $this->prayerTeamServers->add($prayerTeamServer);
            $prayerTeamServer->setEvent($this);
        }

        return $this;
    }

    public function removePrayerTeamServer(EventPrayerTeamServer $prayerTeamServer): static
    {
        if ($this->prayerTeamServers->removeElement($prayerTeamServer)) {
            // set the owning side to null (unless already changed)
            if ($prayerTeamServer->getEvent() === $this) {
                $prayerTeamServer->setEvent(null);
            }
        }

        return $this;
    }

    public function isRegistrationOpen(): ?bool
    {
        return $this->registrationOpen;
    }

    public function setRegistrationOpen(bool $registrationOpen): static
    {
        $this->registrationOpen = $registrationOpen;

        return $this;
    }

    public function getPrayerTeamAssignmentsDeadline(): ?DateTimeInterface
    {
        return $this->prayerTeamAssignmentsDeadline;
    }

    public function setPrayerTeamAssignmentsDeadline(?DateTimeInterface $prayerTeamAssignmentsDeadline): static
    {
        $this->prayerTeamAssignmentsDeadline = $prayerTeamAssignmentsDeadline;

        return $this;
    }

    public function getCheckInToken(): ?string
    {
        return $this->checkInToken;
    }

    public function setCheckInToken(?string $checkInToken): static
    {
        $this->checkInToken = $checkInToken;

        return $this;
    }
}
