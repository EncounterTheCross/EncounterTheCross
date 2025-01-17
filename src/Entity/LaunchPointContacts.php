<?php

namespace App\Entity;

use App\Repository\LaunchPointContactsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LaunchPointContactsRepository::class)]
class LaunchPointContacts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $helper = false;

    #[ORM\ManyToOne(inversedBy: 'launchPointContacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $launchPoint = null;

    #[ORM\OneToOne(inversedBy: 'launchPointContact', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Leader $leader = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isHelper(): ?bool
    {
        return $this->helper;
    }

    public function setHelper(bool $helper): static
    {
        $this->helper = $helper;

        return $this;
    }

    public function getLaunchPoint(): ?Location
    {
        return $this->launchPoint;
    }

    public function setLaunchPoint(?Location $launchPoint): static
    {
        $this->launchPoint = $launchPoint;

        return $this;
    }

    public function getLeader(): ?Leader
    {
        return $this->leader;
    }

    public function setLeader(Leader $leader): static
    {
        $this->leader = $leader;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getLeader();
    }
}
