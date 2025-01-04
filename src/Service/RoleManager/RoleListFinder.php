<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 4/8/23
 * Project: EncounterTheCross
 * File Name: RoleListFinder.php
 */

namespace App\Service\RoleManager;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see https://stackoverflow.com/a/36900807 Original Idea from here
 */
class RoleListFinder
{
    // THIS VARIABLE IS TO STAY UP TO DATE WITH THE SECURITY.YAML TOP ROLE!
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getRolesAccessableToUserOrFullList(UserInterface $user): array
    {
        return $this->getRoles($user->getRoles());
    }

    private function getRoles($originalRoles): array
    {
        return $this->roleHierarchy->getReachableRoleNames($originalRoles);
    }
}
