<?php

namespace App\Entity;

use App\Entity\Traits\CoreEntityTrait;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    use CoreEntityTrait;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'details', targetEntity: ContactPerson::class, orphanRemoval: true)]
    private Collection $contactFor;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: EventParticipant::class, orphanRemoval: true)]
    private Collection $attendedEvents;

    public function __construct()
    {
        $this->contactFor = new ArrayCollection();
        $this->attendedEvents = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection<int, ContactPerson>
     */
    public function getContactFor(): Collection
    {
        return $this->contactFor;
    }

    public function addContactFor(ContactPerson $contactFor): self
    {
        if (!$this->contactFor->contains($contactFor)) {
            $this->contactFor->add($contactFor);
            $contactFor->setDetails($this);
        }

        return $this;
    }

    public function removeContactFor(ContactPerson $contactFor): self
    {
        if ($this->contactFor->removeElement($contactFor)) {
            // set the owning side to null (unless already changed)
            if ($contactFor->getDetails() === $this) {
                $contactFor->setDetails(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventParticipant>
     */
    public function getAttendedEvents(): Collection
    {
        return $this->attendedEvents;
    }

    public function addAttendedEvent(EventParticipant $attendedEvent): self
    {
        if (!$this->attendedEvents->contains($attendedEvent)) {
            $this->attendedEvents->add($attendedEvent);
            $attendedEvent->setPerson($this);
        }

        return $this;
    }

    public function removeAttendedEvent(EventParticipant $attendedEvent): self
    {
        if ($this->attendedEvents->removeElement($attendedEvent)) {
            // set the owning side to null (unless already changed)
            if ($attendedEvent->getPerson() === $this) {
                $attendedEvent->setPerson(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getInitials(): string
    {
        $firstName = $this->getFirstName();
        $lastName = $this->getLastName();

        return strtoupper(
            substr($firstName, 0, 1).
            substr($lastName, 0, 1)
        );
    }

    public function __toString(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
}
