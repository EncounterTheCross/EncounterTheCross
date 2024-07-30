<?php

namespace App\Enum;

enum PrayTeamEnum: string
{
    case TEAM_1 = 'supplies_and_logistics';
    case TEAM_2 = 'registration';
    case TEAM_3 = 'tech_and_venue';
    case TEAM_4 = 'leadership';
    case TEAM_5 = 'nourishment';

    public function getLabel(): string
    {
        return str_replace('And', '&', ucwords(str_replace('_', ' ', $this->value)));
    }
}
