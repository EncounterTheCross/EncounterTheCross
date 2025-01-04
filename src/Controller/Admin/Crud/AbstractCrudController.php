<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 4/7/23
 * Project: EncounterTheCross
 * File Name: AbstractCrudController.php
 */

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Crud\Extended\CoreCrudTrait;
use App\Controller\Admin\Crud\Extended\CrudControllerInterface;
use App\Controller\Admin\Crud\Field\Field;
use App\Controller\Admin\Crud\Field\UuidField;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as BaseAbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

abstract class AbstractCrudController extends BaseAbstractCrudController implements CrudControllerInterface
{
    use CoreCrudTrait;

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // TODO: filter out Deleted Entities unless Role:FULL
        //        $expr = $queryBuilder->expr();
        //        $qb->andWhere(
        //            $expr->isNull('entity.deletedAt')
        //        );

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        // Make sure we assign Uuid types
        $fields = FieldCollection::new(parent::configureFields($pageName));
        $fields
            ->getByProperty('rowPointer')
            ->setFieldFqcn(UuidField::class);

        yield from array_map(
            fn ($dto) => Field::newFromDto($dto),
            array_values($fields->getIterator()->getArrayCopy())
        );
    }

    protected function addRowPointerField(): FieldInterface
    {
        return UuidField::new('rowPointer')
            ->hideOnForm();
    }
}
