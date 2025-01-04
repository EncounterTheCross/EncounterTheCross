<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 12/23/23
 * Project: EncounterTheCross
 * File Name: SubCrudTrait.php
 */

namespace App\Controller\Admin\Crud\Extended;

use App\Exception\Core\RuntimeException;
use App\Exception\CrudLogicException;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

trait SubCrudTrait
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        if (!is_callable(['parent', 'createIndexQueryBuilder'])) {
            throw new CrudLogicException('SubCrud does not have a createIndexQueryBuilder Method.');
        }
        // Call the parent's createIndexQueryBuilder
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $repository = self::getEntityRepositoryFqcn();

        if (!is_callable([$repository, 'queryBuilderFilterToParentId'])) {
            throw new CrudLogicException('SubCrud does not have a Repository with ::queryBuilderFilterToEventId Method.');
        }

        $parentId = $this->getAdminUrlGenerator()
            ->get(ParentCrudControllerInterface::PARENT_ID);

        if (null === $parentId) {
            // double-check the referrer doesn't have the id we need
            throw new RuntimeException('Unable to display Sub CRUD page, we do not know what the parent is.');
        }

        return $repository::queryBuilderFilterToParentId($parentId, $queryBuilder);
    }

    abstract public function getAdminUrlGenerator(): AdminUrlGenerator;

    abstract public static function getEntityRepositoryFqcn(): string;
}
