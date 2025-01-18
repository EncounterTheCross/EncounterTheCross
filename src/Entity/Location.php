<?php

namespace App\Entity;

use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CoreEntityTrait;
use App\Enum\EventParticipantStatusEnum;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    use CoreEntityTrait;
    use AddressTrait;

    public const TYPE_LAUNCH_POINT = 'launch';
    public const TYPE_EVENT = 'event';

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Event::class)]
    private Collection $events; // this field is for self::TYPE_EVENT only

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'launchPoint', targetEntity: EventParticipant::class)]
    private Collection $eventAttendees;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'launchPoints')]
    private Collection $launchPointEvents; // this field is for self::TYPE_LAUNCH_POINT only

    #[ORM\Column(nullable: true)]
    private ?array $geolocation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pinColor = null;

    #[ORM\Column()]
    private bool $active = true;

    /**
     * @var Collection<int, LaunchPointContacts>
     */
    #[ORM\OneToMany(mappedBy: 'launchPoint', targetEntity: LaunchPointContacts::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $launchPointContacts;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->eventAttendees = new ArrayCollection();
        $this->launchPointEvents = new ArrayCollection();
        $this->launchPointContacts = new ArrayCollection();
    }

    public function getShortAddress(): string
    {
        $city = empty($city = $this->getCity() ?? '') ? $city : $city.', ';
        $state = empty($state = $this->getState() ?? '') ? $state : $state.' ';
        $zip = $this->getZipcode() ?? '';

        return $city.$state.$zip;
    }

    public function getLongAddress(bool $url = false): string
    {
        $cityStateZip = $this->getShortAddress();

        if ($url) {
            return urlencode($this->getLine1().', '.$cityStateZip.', '.$this->getCountry());
        }

        return $this->getLine1()."\n".$cityStateZip."\n".$this->getCountry();
    }

    public function getName(bool $truncate = false): ?string
    {
        if ($truncate && (strlen($this->name) > 31)) {
            return substr($this->name, 0, 28).'...';
        }

        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function TYPES(): array
    {
        $oClass = new ReflectionClass(static::class);

        return $oClass->getConstants();
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, self::TYPES())) {
            throw new InvalidArgumentException('Invalid Type provided for Location');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, EventParticipant>
     */
    public function getEventAttendees(): Collection
    {
        return $this->eventAttendees;
    }

    public function getAttendingEventAttendees(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['person.firstName' => Order::Ascending]);

        return $this->getEventAttendees()->filter(function (EventParticipant $eventAttendee) {
            return $eventAttendee->getStatus() === EventParticipantStatusEnum::ATTENDING->value;
        })->matching($criteria);
    }

    public function addEventAttendee(EventParticipant $eventAttendee): self
    {
        if (!$this->eventAttendees->contains($eventAttendee)) {
            $this->eventAttendees->add($eventAttendee);
            $eventAttendee->setLaunchPoint($this);
        }

        return $this;
    }

    public function removeEventAttendee(EventParticipant $eventAttendee): self
    {
        if ($this->eventAttendees->removeElement($eventAttendee)) {
            // set the owning side to null (unless already changed)
            if ($eventAttendee->getLaunchPoint() === $this) {
                $eventAttendee->setLaunchPoint(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getLaunchPointEvents(): Collection
    {
        return $this->launchPointEvents;
    }

    public function addLaunchPointEvent(Event $launchPointEvent): self
    {
        if (!$this->launchPointEvents->contains($launchPointEvent)) {
            $this->launchPointEvents->add($launchPointEvent);
            $launchPointEvent->addLaunchPoint($this);
        }

        return $this;
    }

    public function removeLaunchPointEvent(Event $launchPointEvent): self
    {
        if ($this->launchPointEvents->removeElement($launchPointEvent)) {
            $launchPointEvent->removeLaunchPoint($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    #[ArrayShape(['latitude' => 'int', 'longitude' => 'int', 'color' => 'string', 'status' => 'string'])]
    public function getGeolocation(): ?array
    {
        return $this->geolocation;
    }

    public function setGeolocation(?array $geolocation): static
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    public function hasMappingLocation(): bool
    {
        if (!$this->getGeolocation()) {
            return false;
        }

        if ('SUCCESS' !== $this->getGeolocation()['status']) {
            return false;
        }

        return true;
    }

    public function getLatitude(): ?float
    {
        return $this->getGeolocation()['latitude'] ?? null;
    }

    public function getLongitude(): ?float
    {
        return $this->getGeolocation()['longitude'] ?? null;
    }

    /**
     * @return Collection<int, Leader>
     */
    public function getLaunchPointContacts(): Collection
    {
        return $this->launchPointContacts;
    }

    public function getLaunchPointContactLeaders(): Collection
    {
        $mainLeaders = Criteria::create()
            ->andWhere(
                Criteria::expr()->eq('helper', false),
            );

        return $this->launchPointContacts->matching($mainLeaders);
    }

    public function getLaunchPointContactHelpers(): Collection
    {
        $mainLeaders = Criteria::create()
            ->andWhere(
                Criteria::expr()->eq('helper', true),
            );

        return $this->launchPointContacts->matching($mainLeaders);
    }

    public function addLaunchPointContact(LaunchPointContacts $launchPointContact): static
    {
        if (!$this->launchPointContacts->contains($launchPointContact)) {
            $this->launchPointContacts->add($launchPointContact);
            $launchPointContact->setLaunchPoint($this);
        }

        return $this;
    }

    public function removeLaunchPointContact(LaunchPointContacts $launchPointContact): static
    {
        if ($this->launchPointContacts->removeElement($launchPointContact)) {
            // set the owning side to null (unless already changed)
            if ($launchPointContact->getLaunchPoint() === $this) {
                $launchPointContact->setLaunchPoint(null);
            }
        }

        return $this;
    }

    public function getPinColor(): ?string
    {
        return $this->pinColor;
    }

    public function setPinColor(string $pinColor): static
    {
        $this->pinColor = $pinColor;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getChartData(): ?array
    {
        return [
            'name' => $this->getName(),
            'attendees' => $this->getEventAttendees(),
            'servers' => 15,
        ];
    }
}
