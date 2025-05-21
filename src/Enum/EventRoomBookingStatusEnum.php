<?php

namespace App\Enum;

enum EventRoomBookingStatusEnum: string
{
  case CONFIRMED = 'confirmed';
  case ASSIGNED = 'assigned';
  case PENDING = 'pending';
}