<?php

namespace App\Controller\Admin\Crud;

use App\Entity\LaunchPointContacts;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class LaunchPointContactsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LaunchPointContacts::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            BooleanField::new('helper'),
            AssociationField::new('launchPoint')
                ->hideOnForm(),
            AssociationField::new('leader'),
        ];
    }
}
