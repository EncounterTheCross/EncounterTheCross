<?php

namespace App\Entity\VenueBooking;

use App\Entity\Traits\EntityIdTrait;
use App\Repository\VenueBooking\RoomConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomConfigurationRepository::class)]
class RoomConfiguration
{
    use EntityIdTrait;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?bool $isDefault = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    private ?int $validFromMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $validUntilMonth = null;

    #[ORM\ManyToOne(inversedBy: 'roomConfigurations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getValidFromMonth(): ?int
    {
        return $this->validFromMonth;
    }

    public function setValidFromMonth(?int $validFromMonth): static
    {
        $this->validFromMonth = $validFromMonth;

        return $this;
    }

    public function getValidUntilMonth(): ?int
    {
        return $this->validUntilMonth;
    }

    public function setValidUntilMonth(?int $validUntilMonth): static
    {
        $this->validUntilMonth = $validUntilMonth;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }
}
