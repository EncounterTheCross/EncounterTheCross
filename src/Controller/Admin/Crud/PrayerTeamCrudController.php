<?php

namespace App\Controller\Admin\Crud;

use App\Entity\PrayerTeam;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PrayerTeamCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PrayerTeam::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            BooleanField::new('requiresIntersession')
                ->setPermission('ROLE_DATA_EDITOR_OVERWRITE'),
        ];
    }
}
