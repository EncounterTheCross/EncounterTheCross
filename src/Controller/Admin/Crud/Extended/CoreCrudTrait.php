<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: CrudTrait.php
 */

namespace App\Controller\Admin\Crud\Extended;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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

    protected function getEntityManager(): EntityManager
    {
        /** Registry $reg */
        $reg = $this->container->get('doctrine');
        
        return $reg->getManager('default');
    }
}
