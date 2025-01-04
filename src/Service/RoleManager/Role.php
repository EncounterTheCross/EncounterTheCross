<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 4/8/23
 * Project: EncounterTheCross
 * File Name: Role.php
 */

namespace App\Service\RoleManager;

/**
 * This class is used to create constance of role definitions.
 */
class Role
{
    // Constant ROLE Names
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ADMIN = 'ROLE_ADMIN'; // Just a normal Admin

    public const LEADER = 'ROLE_LEADER';
    public const LEADER_EVENT = 'ROLE_EVENT_LEADER';
    public const LEADER_LAUNCH_POINT = 'ROLE_LAUNCH_LEADER';

    public const USER = 'ROLE_USER'; // Normal User

    /*
     * Constant ROLE Levels
     *
     * FULL: the user role that can do everything
     * LIMITED_FULL: the user role that can do ALMOST all admin things
     */
    public const FULL = self::SUPER_ADMIN;
    public const LIMITED_FULL = self::ADMIN;
    public const DEFAULT = self::USER;
}
