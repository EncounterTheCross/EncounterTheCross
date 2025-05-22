<?php

namespace App\Controller\Admin\VenueBooking;

use App\Controller\Admin\Crud\AbstractCrudController;
use App\Entity\Location;
use App\Entity\VenueBooking\Area;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AreaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Area::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('areaName'),
            AssociationField::new('venue')
                ->setQueryBuilder(
                    fn(QueryBuilder $queryBuilder) => $queryBuilder
                        ->andWhere(
                            $queryBuilder->expr()->eq('entity.type', ':type')
                        )
                        ->andWhere(
                            $queryBuilder->expr()->eq(
                                'entity.active',
                                ':active'
                            )
                        )
                        ->setParameter('active', true)
                        ->setParameter('type', Location::TYPE_EVENT)
                )
            ,
            CollectionField::new('rooms')
                ->hideWhenCreating()
                ->setEntryIsComplex()
                ->useEntryCrudForm()
            ,
        ];
    }
    
}
