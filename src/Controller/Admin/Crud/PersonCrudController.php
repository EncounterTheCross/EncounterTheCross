<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Crud\Field\Field;
use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->setPermissions([
                Action::EDIT => 'ROLE_DATA_EDITOR_OVERWRITE',
            ])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // Email is not here as that field is controlled on LeaderCrud.
        yield Field::new('firstName');
        yield Field::new('lastName');
        yield TelephoneField::new('phone');
    }
}
