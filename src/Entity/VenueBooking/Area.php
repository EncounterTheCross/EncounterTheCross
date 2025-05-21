<?php

namespace App\Entity\VenueBooking;

use App\Entity\Location;
use App\Entity\Traits\CoreEntityTrait;
use App\Repository\VenueBooking\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    use CoreEntityTrait;

    #[ORM\Column(type: 'boolean')]
    private $disabled;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private $areaName;

    #[ORM\Column(type: 'string', length: 30)]
    private $sortKey;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $timezone;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $areaAdminEmail;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $resolution;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $defaultDuration;

    #[ORM\Column(type: 'boolean')]
    private $defaultDurationAllDay;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $morningstarts;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $morningstartsMinutes;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $eveningends;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $eveningendsMinutes;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $privateEnabled;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $privateDefault;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $privateMandatory;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private $privateOverride;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $minCreateAheadEnabled;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $minCreateAheadSecs;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $maxCreateAheadEnabled;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxCreateAheadSecs;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $minDeleteAheadEnabled;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $minDeleteAheadSecs;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $maxDeleteAheadEnabled;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $maxDeleteAheadSecs;

    #[ORM\Column(type: 'boolean')]
    private $maxPerDayEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxPerDay;

    #[ORM\Column(type: 'boolean')]
    private $maxPerWeekEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxPerWeek;

    #[ORM\Column(type: 'boolean')]
    private $maxPerMonthEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxPerMonth;

    #[ORM\Column(type: 'boolean')]
    private $maxPerYearEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxPerYear;

    #[ORM\Column(type: 'boolean')]
    private $maxPerFutureEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxPerFuture;

    #[ORM\Column(type: 'boolean')]
    private $maxSecsPerDayEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxSecsPerDay;

    #[ORM\Column(type: 'boolean')]
    private $maxSecsPerWeekEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxSecsPerWeek;

    #[ORM\Column(type: 'boolean')]
    private $maxSecsPerMonthEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxSecsPerMonth;

    #[ORM\Column(type: 'boolean')]
    private $maxSecsPerYearEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxSecsPerYear;

    #[ORM\Column(type: 'boolean')]
    private $maxSecsPerFutureEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxSecsPerFuture;

    #[ORM\Column(type: 'boolean')]
    private $maxDurationEnabled;

    #[ORM\Column(type: 'integer')]
    private $maxDurationSecs;

    #[ORM\Column(type: 'integer')]
    private $maxDurationPeriods;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $customHtml;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approvalEnabled;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $remindersEnabled;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enablePeriods;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $periods;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $confirmationEnabled;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $confirmedDefault;

    #[ORM\Column(type: 'boolean')]
    private $timesAlongTop;

    #[ORM\Column(type: 'string', length: 1)]
    private $defaultType;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'area', orphanRemoval: true)]
    private Collection $rooms;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $venue = null;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getAreaName(): ?string
    {
        return $this->areaName;
    }

    public function setAreaName(?string $areaName): static
    {
        $this->areaName = $areaName;

        return $this;
    }

    public function getSortKey(): ?string
    {
        return $this->sortKey;
    }

    public function setSortKey(string $sortKey): static
    {
        $this->sortKey = $sortKey;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getAreaAdminEmail(): ?string
    {
        return $this->areaAdminEmail;
    }

    public function setAreaAdminEmail(?string $areaAdminEmail): static
    {
        $this->areaAdminEmail = $areaAdminEmail;

        return $this;
    }

    public function getResolution(): ?int
    {
        return $this->resolution;
    }

    public function setResolution(?int $resolution): static
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getDefaultDuration(): ?int
    {
        return $this->defaultDuration;
    }

    public function setDefaultDuration(?int $defaultDuration): static
    {
        $this->defaultDuration = $defaultDuration;

        return $this;
    }

    public function isDefaultDurationAllDay(): ?bool
    {
        return $this->defaultDurationAllDay;
    }

    public function setDefaultDurationAllDay(bool $defaultDurationAllDay): static
    {
        $this->defaultDurationAllDay = $defaultDurationAllDay;

        return $this;
    }

    public function getMorningstarts(): ?int
    {
        return $this->morningstarts;
    }

    public function setMorningstarts(?int $morningstarts): static
    {
        $this->morningstarts = $morningstarts;

        return $this;
    }

    public function getMorningstartsMinutes(): ?int
    {
        return $this->morningstartsMinutes;
    }

    public function setMorningstartsMinutes(?int $morningstartsMinutes): static
    {
        $this->morningstartsMinutes = $morningstartsMinutes;

        return $this;
    }

    public function getEveningends(): ?int
    {
        return $this->eveningends;
    }

    public function setEveningends(?int $eveningends): static
    {
        $this->eveningends = $eveningends;

        return $this;
    }

    public function getEveningendsMinutes(): ?int
    {
        return $this->eveningendsMinutes;
    }

    public function setEveningendsMinutes(?int $eveningendsMinutes): static
    {
        $this->eveningendsMinutes = $eveningendsMinutes;

        return $this;
    }

    public function isPrivateEnabled(): ?bool
    {
        return $this->privateEnabled;
    }

    public function setPrivateEnabled(?bool $privateEnabled): static
    {
        $this->privateEnabled = $privateEnabled;

        return $this;
    }

    public function isPrivateDefault(): ?bool
    {
        return $this->privateDefault;
    }

    public function setPrivateDefault(?bool $privateDefault): static
    {
        $this->privateDefault = $privateDefault;

        return $this;
    }

    public function isPrivateMandatory(): ?bool
    {
        return $this->privateMandatory;
    }

    public function setPrivateMandatory(?bool $privateMandatory): static
    {
        $this->privateMandatory = $privateMandatory;

        return $this;
    }

    public function getPrivateOverride(): ?string
    {
        return $this->privateOverride;
    }

    public function setPrivateOverride(?string $privateOverride): static
    {
        $this->privateOverride = $privateOverride;

        return $this;
    }

    public function isMinCreateAheadEnabled(): ?bool
    {
        return $this->minCreateAheadEnabled;
    }

    public function setMinCreateAheadEnabled(?bool $minCreateAheadEnabled): static
    {
        $this->minCreateAheadEnabled = $minCreateAheadEnabled;

        return $this;
    }

    public function getMinCreateAheadSecs(): ?int
    {
        return $this->minCreateAheadSecs;
    }

    public function setMinCreateAheadSecs(?int $minCreateAheadSecs): static
    {
        $this->minCreateAheadSecs = $minCreateAheadSecs;

        return $this;
    }

    public function isMaxCreateAheadEnabled(): ?bool
    {
        return $this->maxCreateAheadEnabled;
    }

    public function setMaxCreateAheadEnabled(?bool $maxCreateAheadEnabled): static
    {
        $this->maxCreateAheadEnabled = $maxCreateAheadEnabled;

        return $this;
    }

    public function getMaxCreateAheadSecs(): ?int
    {
        return $this->maxCreateAheadSecs;
    }

    public function setMaxCreateAheadSecs(?int $maxCreateAheadSecs): static
    {
        $this->maxCreateAheadSecs = $maxCreateAheadSecs;

        return $this;
    }

    public function isMinDeleteAheadEnabled(): ?bool
    {
        return $this->minDeleteAheadEnabled;
    }

    public function setMinDeleteAheadEnabled(?bool $minDeleteAheadEnabled): static
    {
        $this->minDeleteAheadEnabled = $minDeleteAheadEnabled;

        return $this;
    }

    public function getMinDeleteAheadSecs(): ?int
    {
        return $this->minDeleteAheadSecs;
    }

    public function setMinDeleteAheadSecs(?int $minDeleteAheadSecs): static
    {
        $this->minDeleteAheadSecs = $minDeleteAheadSecs;

        return $this;
    }

    public function isMaxDeleteAheadEnabled(): ?bool
    {
        return $this->maxDeleteAheadEnabled;
    }

    public function setMaxDeleteAheadEnabled(?bool $maxDeleteAheadEnabled): static
    {
        $this->maxDeleteAheadEnabled = $maxDeleteAheadEnabled;

        return $this;
    }

    public function getMaxDeleteAheadSecs(): ?int
    {
        return $this->maxDeleteAheadSecs;
    }

    public function setMaxDeleteAheadSecs(?int $maxDeleteAheadSecs): static
    {
        $this->maxDeleteAheadSecs = $maxDeleteAheadSecs;

        return $this;
    }

    public function isMaxPerDayEnabled(): ?bool
    {
        return $this->maxPerDayEnabled;
    }

    public function setMaxPerDayEnabled(bool $maxPerDayEnabled): static
    {
        $this->maxPerDayEnabled = $maxPerDayEnabled;

        return $this;
    }

    public function getMaxPerDay(): ?int
    {
        return $this->maxPerDay;
    }

    public function setMaxPerDay(int $maxPerDay): static
    {
        $this->maxPerDay = $maxPerDay;

        return $this;
    }

    public function isMaxPerWeekEnabled(): ?bool
    {
        return $this->maxPerWeekEnabled;
    }

    public function setMaxPerWeekEnabled(bool $maxPerWeekEnabled): static
    {
        $this->maxPerWeekEnabled = $maxPerWeekEnabled;

        return $this;
    }

    public function getMaxPerWeek(): ?int
    {
        return $this->maxPerWeek;
    }

    public function setMaxPerWeek(int $maxPerWeek): static
    {
        $this->maxPerWeek = $maxPerWeek;

        return $this;
    }

    public function isMaxPerMonthEnabled(): ?bool
    {
        return $this->maxPerMonthEnabled;
    }

    public function setMaxPerMonthEnabled(bool $maxPerMonthEnabled): static
    {
        $this->maxPerMonthEnabled = $maxPerMonthEnabled;

        return $this;
    }

    public function getMaxPerMonth(): ?int
    {
        return $this->maxPerMonth;
    }

    public function setMaxPerMonth(int $maxPerMonth): static
    {
        $this->maxPerMonth = $maxPerMonth;

        return $this;
    }

    public function isMaxPerYearEnabled(): ?bool
    {
        return $this->maxPerYearEnabled;
    }

    public function setMaxPerYearEnabled(bool $maxPerYearEnabled): static
    {
        $this->maxPerYearEnabled = $maxPerYearEnabled;

        return $this;
    }

    public function getMaxPerYear(): ?int
    {
        return $this->maxPerYear;
    }

    public function setMaxPerYear(int $maxPerYear): static
    {
        $this->maxPerYear = $maxPerYear;

        return $this;
    }

    public function isMaxPerFutureEnabled(): ?bool
    {
        return $this->maxPerFutureEnabled;
    }

    public function setMaxPerFutureEnabled(bool $maxPerFutureEnabled): static
    {
        $this->maxPerFutureEnabled = $maxPerFutureEnabled;

        return $this;
    }

    public function getMaxPerFuture(): ?int
    {
        return $this->maxPerFuture;
    }

    public function setMaxPerFuture(int $maxPerFuture): static
    {
        $this->maxPerFuture = $maxPerFuture;

        return $this;
    }

    public function isMaxSecsPerDayEnabled(): ?bool
    {
        return $this->maxSecsPerDayEnabled;
    }

    public function setMaxSecsPerDayEnabled(bool $maxSecsPerDayEnabled): static
    {
        $this->maxSecsPerDayEnabled = $maxSecsPerDayEnabled;

        return $this;
    }

    public function getMaxSecsPerDay(): ?int
    {
        return $this->maxSecsPerDay;
    }

    public function setMaxSecsPerDay(int $maxSecsPerDay): static
    {
        $this->maxSecsPerDay = $maxSecsPerDay;

        return $this;
    }

    public function isMaxSecsPerWeekEnabled(): ?bool
    {
        return $this->maxSecsPerWeekEnabled;
    }

    public function setMaxSecsPerWeekEnabled(bool $maxSecsPerWeekEnabled): static
    {
        $this->maxSecsPerWeekEnabled = $maxSecsPerWeekEnabled;

        return $this;
    }

    public function getMaxSecsPerWeek(): ?int
    {
        return $this->maxSecsPerWeek;
    }

    public function setMaxSecsPerWeek(int $maxSecsPerWeek): static
    {
        $this->maxSecsPerWeek = $maxSecsPerWeek;

        return $this;
    }

    public function isMaxSecsPerMonthEnabled(): ?bool
    {
        return $this->maxSecsPerMonthEnabled;
    }

    public function setMaxSecsPerMonthEnabled(bool $maxSecsPerMonthEnabled): static
    {
        $this->maxSecsPerMonthEnabled = $maxSecsPerMonthEnabled;

        return $this;
    }

    public function getMaxSecsPerMonth(): ?int
    {
        return $this->maxSecsPerMonth;
    }

    public function setMaxSecsPerMonth(int $maxSecsPerMonth): static
    {
        $this->maxSecsPerMonth = $maxSecsPerMonth;

        return $this;
    }

    public function isMaxSecsPerYearEnabled(): ?bool
    {
        return $this->maxSecsPerYearEnabled;
    }

    public function setMaxSecsPerYearEnabled(bool $maxSecsPerYearEnabled): static
    {
        $this->maxSecsPerYearEnabled = $maxSecsPerYearEnabled;

        return $this;
    }

    public function getMaxSecsPerYear(): ?int
    {
        return $this->maxSecsPerYear;
    }

    public function setMaxSecsPerYear(int $maxSecsPerYear): static
    {
        $this->maxSecsPerYear = $maxSecsPerYear;

        return $this;
    }

    public function isMaxSecsPerFutureEnabled(): ?bool
    {
        return $this->maxSecsPerFutureEnabled;
    }

    public function setMaxSecsPerFutureEnabled(bool $maxSecsPerFutureEnabled): static
    {
        $this->maxSecsPerFutureEnabled = $maxSecsPerFutureEnabled;

        return $this;
    }

    public function getMaxSecsPerFuture(): ?int
    {
        return $this->maxSecsPerFuture;
    }

    public function setMaxSecsPerFuture(int $maxSecsPerFuture): static
    {
        $this->maxSecsPerFuture = $maxSecsPerFuture;

        return $this;
    }

    public function isMaxDurationEnabled(): ?bool
    {
        return $this->maxDurationEnabled;
    }

    public function setMaxDurationEnabled(bool $maxDurationEnabled): static
    {
        $this->maxDurationEnabled = $maxDurationEnabled;

        return $this;
    }

    public function getMaxDurationSecs(): ?int
    {
        return $this->maxDurationSecs;
    }

    public function setMaxDurationSecs(int $maxDurationSecs): static
    {
        $this->maxDurationSecs = $maxDurationSecs;

        return $this;
    }

    public function getMaxDurationPeriods(): ?int
    {
        return $this->maxDurationPeriods;
    }

    public function setMaxDurationPeriods(int $maxDurationPeriods): static
    {
        $this->maxDurationPeriods = $maxDurationPeriods;

        return $this;
    }

    public function getCustomHtml(): ?string
    {
        return $this->customHtml;
    }

    public function setCustomHtml(?string $customHtml): static
    {
        $this->customHtml = $customHtml;

        return $this;
    }

    public function isApprovalEnabled(): ?bool
    {
        return $this->approvalEnabled;
    }

    public function setApprovalEnabled(?bool $approvalEnabled): static
    {
        $this->approvalEnabled = $approvalEnabled;

        return $this;
    }

    public function isRemindersEnabled(): ?bool
    {
        return $this->remindersEnabled;
    }

    public function setRemindersEnabled(?bool $remindersEnabled): static
    {
        $this->remindersEnabled = $remindersEnabled;

        return $this;
    }

    public function isEnablePeriods(): ?bool
    {
        return $this->enablePeriods;
    }

    public function setEnablePeriods(?bool $enablePeriods): static
    {
        $this->enablePeriods = $enablePeriods;

        return $this;
    }

    public function getPeriods(): ?string
    {
        return $this->periods;
    }

    public function setPeriods(?string $periods): static
    {
        $this->periods = $periods;

        return $this;
    }

    public function isConfirmationEnabled(): ?bool
    {
        return $this->confirmationEnabled;
    }

    public function setConfirmationEnabled(?bool $confirmationEnabled): static
    {
        $this->confirmationEnabled = $confirmationEnabled;

        return $this;
    }

    public function isConfirmedDefault(): ?bool
    {
        return $this->confirmedDefault;
    }

    public function setConfirmedDefault(?bool $confirmedDefault): static
    {
        $this->confirmedDefault = $confirmedDefault;

        return $this;
    }

    public function isTimesAlongTop(): ?bool
    {
        return $this->timesAlongTop;
    }

    public function setTimesAlongTop(bool $timesAlongTop): static
    {
        $this->timesAlongTop = $timesAlongTop;

        return $this;
    }

    public function getDefaultType(): ?string
    {
        return $this->defaultType;
    }

    public function setDefaultType(string $defaultType): static
    {
        $this->defaultType = $defaultType;

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setArea($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getArea() === $this) {
                $room->setArea(null);
            }
        }

        return $this;
    }

    public function getVenue(): ?Location
    {
        return $this->venue;
    }

    public function setVenue(?Location $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

}
