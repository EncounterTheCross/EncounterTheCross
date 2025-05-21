<?php

namespace App\Service\VenueBooking;

use App\Entity\VenueBooking\Booking;
use App\Entity\VenueBooking\Room;
use App\Entity\VenueBooking\RoomConfiguration;
use App\Repository\VenueBooking\RoomConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomConfigurationService
{
    private $entityManager;
    private $roomConfigurationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoomConfigurationRepository $roomConfigurationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->roomConfigurationRepository = $roomConfigurationRepository;
    }

    /**
     * Get all available configurations for a room on a specific date
     */
    public function getAvailableConfigurationsForDate(Room $room, \DateTimeInterface $date): array
    {
        return $this->roomConfigurationRepository->findConfigurationsForRoomAndDate($room->getId(), $date);
    }

    /**
     * Get the default configuration for a room on a specific date
     */
    public function getDefaultConfigurationForDate(Room $room, \DateTimeInterface $date): ?RoomConfiguration
    {
        return $this->roomConfigurationRepository->findDefaultConfigurationForDate($room->getId(), $date);
    }

    /**
     * Find the most suitable configuration for a booking based on attendee count
     */
    public function findSuitableConfiguration(Room $room, \DateTimeInterface $date, int $attendeeCount): ?RoomConfiguration
    {
        $configurations = $this->getAvailableConfigurationsForDate($room, $date);
        
        // Filter configurations by capacity
        $suitableConfigurations = array_filter($configurations, function($config) use ($attendeeCount) {
            return $config->getCapacity() >= $attendeeCount;
        });
        
        if (empty($suitableConfigurations)) {
            return null;
        }
        
        // Sort by capacity (ascending) to find the smallest suitable configuration
        usort($suitableConfigurations, function($a, $b) {
            return $a->getCapacity() <=> $b->getCapacity();
        });
        
        return reset($suitableConfigurations);
    }
}