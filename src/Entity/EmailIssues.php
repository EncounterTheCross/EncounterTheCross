<?php

namespace App\Entity;

use App\Repository\EmailIssuesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CoreEntityTrait;

#[ORM\Entity(repositoryClass: EmailIssuesRepository::class)]
class EmailIssues
{
    use CoreEntityTrait;

    #[ORM\Column(length: 255)]
    private ?string $sentTo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $error = null;

    #[ORM\Column(length: 255)]
    private ?string $errorStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentTo(): ?string
    {
        return $this->sentTo;
    }

    public function setSentTo(string $sentTo): static
    {
        $this->sentTo = $sentTo;

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

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): static
    {
        $this->error = $error;

        return $this;
    }

    public function getErrorStatus(): ?string
    {
        return $this->errorStatus;
    }

    public function setErrorStatus(string $errorStatus): static
    {
        $this->errorStatus = $errorStatus;

        return $this;
    }
}
