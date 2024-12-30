<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: CrudTrait.php
 */

namespace App\Controller\Admin\Crud\Extended;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CoreCrudTrait
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->container->get(AdminUrlGenerator::class);
    }
}
