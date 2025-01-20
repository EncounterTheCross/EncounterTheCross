<?php

namespace App\Entity;

use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CoreEntityTrait;
use App\Entity\Traits\QuestionsAndConcernsTrait;
use App\Enum\EventParticipantStatusEnum;
use App\Repository\EventParticipantRepository;
use App\Service\Exporter\EntityExportableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventParticipantRepository::class)]
class EventParticipant implements EntityExportableInterface
{
    use CoreEntityTrait;
    use AddressTrait;
    use QuestionsAndConcernsTrait;

    public const TYPE_SERVER = 'server';
    public const TYPE_ATTENDEE = 'attendee';

    public const PAYMENT_METHOD_ATDOOR = 'DOOR';
    public const PAYMENT_METHOD_SCHOLARSHIP = 'SCHOLARSHIP';
    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_ATDOOR,
        self::PAYMENT_METHOD_SCHOLARSHIP,
    ];

    private bool $forceNewPerson = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $church = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invitedBy = null;

    #[ORM\ManyToOne(
        cascade: ['persist'],
        inversedBy: 'eventAttendees'
    )]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(
        message: 'Please choose where you will launch from.'
    )]
    private ?Location $launchPoint = null;

    #[ORM\ManyToOne(
        cascade: ['persist'],
        inversedBy: 'attendedEvents'
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(
        cascade: ['persist'],
        inversedBy: 'eventParticipants'
    )]
    private ?ContactPerson $attendeeContactPerson = null;

    #[ORM\Column(nullable: true)]
    private ?int $serverAttendedTimes = null;

    #[ORM\ManyToOne(inversedBy: 'eventParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?bool $paid = false;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(
        message: 'Please select how you will pay.',
    )]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: 'string', enumType: EventParticipantStatusEnum::class)]
    private EventParticipantStatusEnum $status = EventParticipantStatusEnum::ATTENDING;

    /**
     * @var Collection<int, EventPrayerTeamServer>
     */
    #[ORM\OneToMany(mappedBy: 'EventParticipant', targetEntity: EventPrayerTeamServer::class, cascade: ['persist'])]
    private Collection $eventPrayerTeamServers;

    public function __construct()
    {
        $this->eventPrayerTeamServers = new ArrayCollection();
    }

    public static function TYPES(): array
    {
        return [
            self::TYPE_SERVER,
            self::TYPE_ATTENDEE,
        ];
    }

    public function getChurch(): ?string
    {
        return $this->church;
    }

    public function setChurch(string $church): self
    {
        $this->church = $church;

        return $this;
    }

    public function getInvitedBy(): ?string
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?string $invitedBy): self
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    public function getLaunchPoint(): ?Location
    {
        return $this->launchPoint;
    }

    public function setLaunchPoint(?Location $launchPoint): self
    {
        $this->launchPoint = $launchPoint;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function isServer(): bool
    {
        return self::TYPE_SERVER === $this->getType();
    }

    public function isAttendee(): bool
    {
        return self::TYPE_ATTENDEE === $this->getType();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAttendeeContactPerson(): ?ContactPerson
    {
        return $this->attendeeContactPerson;
    }

    public function setAttendeeContactPerson(?ContactPerson $attendeeContactPerson): self
    {
        $this->attendeeContactPerson = $attendeeContactPerson;

        return $this;
    }

    public function getServerAttendedTimes(): ?int
    {
        return $this->serverAttendedTimes;
    }

    public function setServerAttendedTimes(?int $serverAttendedTimes): self
    {
        $this->serverAttendedTimes = $serverAttendedTimes;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getPerson()->getFullName();
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getExtendedSerialization(): array
    {
        $address = $this->getLine1().PHP_EOL
            .$this->getLine2().PHP_EOL
            .$this->getCity().', '.$this->getState().', '.$this->getZipcode().PHP_EOL;

        $contactPerson = $this->getAttendeeContactPerson();
        $training = $this->getCurrentEventPrayerTeamServer();

        return [
            'status' => $this->getStatus(),
            'type' => $this->getType(),
            'name' => $this->getFullName(),
            'email' => $this->getPerson()->getEmail(),
            'phone' => $this->getPerson()->getPhone(),
            'address' => $address,
            'contactPerson' => $contactPerson?->getDetails()->getFullName(),
            'contactRelation' => $contactPerson?->getRelationship(),
            'contactPhone' => $contactPerson?->getDetails()->getPhone(),
            'invitedBy' => $this->getInvitedBy(),
            'primaryChurch' => $this->getChurch(),
            'servedTimes' => $this->getServerAttendedTimes(),
            'concerns?' => $this->getHealthConcerns(),
            'questions' => $this->getQuestionsOrComments(),
            'launchPoint' => $this->getLaunchPoint()?->getName(),
            'paid' => $this->paid ? 'X' : '',
            'paymentMethod' => $this->paymentMethod ?? '',
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            // prayer team
            'prayerTeam' => $training->getPrayerTeam()?->getName() ?? 'NOT ASSIGNED',
            // server training check in
            'attendedJointTraining' => $training->isCheckedIn() ? 'Yes' : 'No',
        ];
    }

    public function getBasicSerialization(): array
    {
        return [
            'status' => $this->getStatus(),
            'type' => $this->getType(),
            'name' => $this->getFullName(),
            'email' => $this->getPerson()->getEmail(),
            'phone' => $this->getPerson()->getPhone(),
            'contactPerson' => $this->getAttendeeContactPerson()?->getDetails()->getFullName(),
            'contactRelation' => $this->getAttendeeContactPerson()?->getRelationship(),
            'contactPhone' => $this->getAttendeeContactPerson()?->getDetails()->getPhone(),
            'invitedBy' => $this->getInvitedBy(),
            'paid' => $this->paid ? 'X' : '',
            'paymentMethod' => $this->paymentMethod ?? '',
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status->value;
    }

    public function setStatus(string $status): static
    {
        $statusEnum = EventParticipantStatusEnum::tryFrom($status);
        $this->status = $statusEnum;

        return $this;
    }

    public function isForceNewPerson(): bool
    {
        return $this->forceNewPerson;
    }

    public function setForceNewPerson(bool $forceNewPerson): void
    {
        $this->forceNewPerson = $forceNewPerson;
    }

    /**
     * @return Collection<int, EventPrayerTeamServer>
     */
    public function getEventPrayerTeamServers(): Collection
    {
        return $this->eventPrayerTeamServers;
    }

    public function getCurrentEventPrayerTeamServer(): ?EventPrayerTeamServer
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('event', $this->event));

        if (count($this->eventPrayerTeamServers) <= 0) {
            return null;
        }

        return $this->eventPrayerTeamServers->matching($criteria)->first();
    }

    public function addEventPrayerTeamServer(EventPrayerTeamServer $eventPrayerTeamServer): static
    {
        if ($this->eventPrayerTeamServers->contains($eventPrayerTeamServer)) {
            return $this;
        }

        if (null === $eventPrayerTeamServer->getEvent() && null !== $this->getEvent()) {
            $eventPrayerTeamServer->setEvent($this->getEvent());
        }

        $this->eventPrayerTeamServers->add($eventPrayerTeamServer);
        $eventPrayerTeamServer->setEventParticipant($this);

        return $this;
    }

    public function removeEventPrayerTeamServer(EventPrayerTeamServer $eventPrayerTeamServer): static
    {
        if ($this->eventPrayerTeamServers->removeElement($eventPrayerTeamServer)) {
            // set the owning side to null (unless already changed)
            if ($eventPrayerTeamServer->getEventParticipant() === $this) {
                $eventPrayerTeamServer->setEventParticipant(null);
            }
        }

        return $this;
    }
}
