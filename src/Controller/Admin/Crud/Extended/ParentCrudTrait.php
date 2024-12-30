<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: ParentCrudTrait.php
 */

namespace App\Controller\Admin\Crud\Extended;

use App\Enum\EventParticipantStatusEnum;
use App\Exception\Core\LogicException;
use App\Exception\Core\RuntimeException;
use App\Exception\CrudLogicException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait ParentCrudTrait
{
    public function redirectToShowSubCrud(AdminContext $adminContext): RedirectResponse
    {
        // Validate the sub crud exists
        if (!class_exists($this->getSubCrudControllerClass())) {
            throw new LogicException("{$this->getSubCrudControllerClass()} does not Exist. No way for Parent Crud to connect to non existing class crud.");
        }

        $entity = $adminContext->getEntity()->getInstance();

        // Validate Entity Type
        $reflector = new ReflectionClass(get_class($this));

        if (!in_array(ParentCrudControllerInterface::class, $reflector->getInterfaceNames())) {
            throw new CrudLogicException('Entity of Crud not using '.ParentCrudControllerInterface::class.'.');
        }

        $entityId = $entity->getId();

        if (null === $entityId) {
            throw new RuntimeException('The parent entity has not been saved yet. No way to load child entities.');
        }

        $subCrudUrl = $this->getAdminUrlGenerator()
            ->set(ParentCrudControllerInterface::PARENT_ID, $entityId)
            // &filters[status][comparison]==&filters[status][value]=attending
            ->set('filters[status][comparison]', '=')
            ->set('filters[status][value]', EventParticipantStatusEnum::ATTENDING->value)
            ->setController($this->getSubCrudControllerClass())
            ->setAction(Crud::PAGE_INDEX)
        ;
        if ($subCrudUrl->get('entityId')) {
            $subCrudUrl->unset('entityId');
        }

        // Redirect to the Crud Controller
        return $this->redirect($subCrudUrl);
    }

    /**
     * @note Requiring the Symfony Framework Abstract Controller Redirect Method
     */
    abstract protected function redirect(string $url, int $status = 302): RedirectResponse;

    abstract protected function getSubCrudControllerClass(): string;

    abstract protected function getAdminUrlGenerator(): AdminUrlGenerator;
}
