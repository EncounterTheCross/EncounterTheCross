<?php

namespace App\Entity;

use App\Entity\VenueBooking\RoomConfiguration;
use App\Enum\EventRoomBookingStatusEnum;
use App\Repository\EventRoomBookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRoomBookingRepository::class)]
class EventRoomBooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: EventRoomBookingStatusEnum::class)]
    private ?EventRoomBookingStatusEnum $status = EventRoomBookingStatusEnum::PENDING;

    #[ORM\ManyToOne(inversedBy: 'roomBookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RoomConfiguration $roomConfig = null;

    /**
     * @var Collection<int, RoomConfiguration>
     */
    #[ORM\ManyToMany(targetEntity: RoomConfiguration::class)]
    private Collection $rooms;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?location $launchPoint = null;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?EventRoomBookingStatusEnum
    {
        return $this->status;
    }

    public function setStatus(EventRoomBookingStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getRoomConfig(): ?RoomConfiguration
    {
        return $this->roomConfig;
    }

    public function setRoomConfig(?RoomConfiguration $roomConfig): static
    {
        $this->roomConfig = $roomConfig;

        return $this;
    }

    /**
     * @return Collection<int, RoomConfiguration>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(RoomConfiguration $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
        }

        return $this;
    }

    public function removeRoom(RoomConfiguration $room): static
    {
        $this->rooms->removeElement($room);

        return $this;
    }

    public function getLaunchPoint(): ?location
    {
        return $this->launchPoint;
    }

    public function setLaunchPoint(?location $launchPoint): static
    {
        $this->launchPoint = $launchPoint;

        return $this;
    }
}
