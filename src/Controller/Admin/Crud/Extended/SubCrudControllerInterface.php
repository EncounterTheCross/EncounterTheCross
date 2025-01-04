<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: SubCrudControllerInterface.php
 */

namespace App\Controller\Admin\Crud\Extended;

interface SubCrudControllerInterface extends CrudControllerInterface
{
    public static function getEntityRepositoryFqcn(): string;
}
