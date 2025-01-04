<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: ParentCrudControllerInterface.php
 */

namespace App\Controller\Admin\Crud\Extended;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

interface ParentCrudControllerInterface extends CrudControllerInterface
{
    public const PARENT_ID = 'crud_parent_entity_id';

    public function redirectToShowSubCrud(AdminContext $adminContext);
}
