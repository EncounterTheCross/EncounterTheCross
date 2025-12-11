<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/1/24
 * Project: EncounterTheCross
 * File Name: EventParticipantStatusEnum.php
 */

namespace App\Enum;

enum EventParticipantStatusEnum: string
{
    case ATTENDING = 'attending';
    case DROPPED = 'dropped';
    case DUPLICATE = 'duplicate';
    case SPAM = 'spam';
}
