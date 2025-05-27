<?php

namespace App\Controller\Admin\VenueBooking;

use App\Controller\Admin\Crud\AbstractCrudController;
use App\Entity\VenueBooking\Room;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Room::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('roomName'),
            AssociationField::new('area')
            ,
            CollectionField::new(('roomConfigurations'))
                ->hideWhenCreating()
                ->setEntryIsComplex()
                ->useEntryCrudForm(RoomConfigurationCrudController::class)
            ,
        ];
    }
    
}
