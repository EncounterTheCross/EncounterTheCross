<?php

namespace App\Entity\VenueBooking;

use App\Entity\Traits\CoreEntityTrait;
use App\Repository\VenueBooking\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    use CoreEntityTrait;

    // #[ORM\Column(type: 'boolean')]
    // private $disabled;

    #[ORM\Column(type: 'string', length: 25)]
    private $roomName;

    // #[ORM\Column(type: 'string', length: 25)]
    // private $sortKey;

    // #[ORM\Column(type: 'string', length: 60, nullable: true)]
    // private $description;

    // #[ORM\Column(type: 'integer')]
    // private $capacity;

    // #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    // private $roomAdminEmail;

    // #[ORM\Column(type: 'string', length: 255, nullable: true)]
    // private $invalidTypes;

    // #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    // private $customHtml;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $area = null;

    /**
     * @var Collection<int, RoomConfiguration>
     */
    #[ORM\OneToMany(targetEntity: RoomConfiguration::class, mappedBy: 'room', orphanRemoval: true)]
    private Collection $roomConfigurations;

    public function __construct()
    {
        $this->roomConfigurations = new ArrayCollection();
    }

    // #[ORM\ManyToOne(targetEntity: Area::class)]
    // #[ORM\JoinColumn(name: 'area_id', referencedColumnName: 'id')]
    // private $area;

    // public function isDisabled(): ?bool
    // {
    //     return $this->disabled;
    // }

    // public function setDisabled(bool $disabled): static
    // {
    //     $this->disabled = $disabled;

    //     return $this;
    // }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): static
    {
        $this->roomName = $roomName;

        return $this;
    }

    // public function getSortKey(): ?string
    // {
    //     return $this->sortKey;
    // }

    // public function setSortKey(string $sortKey): static
    // {
    //     $this->sortKey = $sortKey;

    //     return $this;
    // }

    // public function getDescription(): ?string
    // {
    //     return $this->description;
    // }

    // public function setDescription(?string $description): static
    // {
    //     $this->description = $description;

    //     return $this;
    // }

    // public function getCapacity(): ?int
    // {
    //     return $this->capacity;
    // }

    // public function setCapacity(int $capacity): static
    // {
    //     $this->capacity = $capacity;

    //     return $this;
    // }

    // public function getRoomAdminEmail(): ?string
    // {
    //     return $this->roomAdminEmail;
    // }

    // public function setRoomAdminEmail(?string $roomAdminEmail): static
    // {
    //     $this->roomAdminEmail = $roomAdminEmail;

    //     return $this;
    // }

    // public function getInvalidTypes(): ?string
    // {
    //     return $this->invalidTypes;
    // }

    // public function setInvalidTypes(?string $invalidTypes): static
    // {
    //     $this->invalidTypes = $invalidTypes;

    //     return $this;
    // }

    // public function getCustomHtml(): ?string
    // {
    //     return $this->customHtml;
    // }

    // public function setCustomHtml(?string $customHtml): static
    // {
    //     $this->customHtml = $customHtml;

    //     return $this;
    // }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection<int, RoomConfiguration>
     */
    public function getRoomConfigurations(): Collection
    {
        return $this->roomConfigurations;
    }

    public function addRoomConfiguration(RoomConfiguration $roomConfiguration): static
    {
        if (!$this->roomConfigurations->contains($roomConfiguration)) {
            $this->roomConfigurations->add($roomConfiguration);
            $roomConfiguration->setRoom($this);
        }

        return $this;
    }

    public function removeRoomConfiguration(RoomConfiguration $roomConfiguration): static
    {
        if ($this->roomConfigurations->removeElement($roomConfiguration)) {
            // set the owning side to null (unless already changed)
            if ($roomConfiguration->getRoom() === $this) {
                $roomConfiguration->setRoom(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->roomName;
    }
}
