<?php

namespace App\Controller\Admin\VenueBooking;

use App\Controller\Admin\Crud\AbstractCrudController;
use App\Entity\VenueBooking\RoomConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RoomConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RoomConfiguration::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextEditorField::new('description'),
            NumberField::new('capacity'),
            BooleanField::new('isDefault'),
            BooleanField::new('isActive'),
            AssociationField::new('room'),

        ];
    }
    
}
