<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Crud\Field\Field;
use App\Entity\Event;
use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;

class LocationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
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
        yield Field::new('name');
        // TODO look into a HiddenField::class
        yield ChoiceField::new('type')
            ->renderAsNativeWidget()
            ->setChoices(array_combine(Location::TYPES(), Location::TYPES()))
            ->onlyOnForms()
            ->setFormTypeOption(
                'disabled',
                'disabled'
            )
            ->setFormTypeOption(
                'row_attr.style',
                'display:none;'
            )
        ;
        // use for Event Location CRUD
        //        yield Field::new('events');
        // use for Launch Point CRUD
        //        yield Field::new('launchPointEvents');

        // Where is the Event Location
        yield Field::new('shortAddress', 'Where is it?')
            ->onlyOnIndex();

        if ($this->isLaunchPoint()) {
            yield AssociationField::new('eventAttendees', 'LifeTime Attendees')
                ->hideOnForm()
            ;
            yield AssociationField::new('launchPointEvents')
                ->autocomplete()
            ;
            yield AssociationField::new('launchPointContacts')
                ->setFormTypeOptionIfNotSet('by_reference', false)
                ->autocomplete()
//                ->allowMultipleChoices()
            ;
            yield ColorField::new('pinColor');
        }

        if ($this->isEventLocation()) {
            // use for Event Location CRUD
            yield CollectionField::new('events')
                ->hideOnForm();
        }

        // address fields
        yield Field::new('line1', 'Address Line 1')
            ->hideOnIndex()
        ;
        yield Field::new('line2', 'Address Line 2')
            ->hideOnIndex()
        ;
        yield Field::new('city')
            ->hideOnIndex()
        ;
        yield Field::new('state')
            ->hideOnIndex()
        ;
        yield Field::new('zipcode')
            ->hideOnIndex()
        ;
        yield Field::new('country')
            ->hideOnIndex()
        ;
        yield BooleanField::new('active');
    }

    protected function isEventLocation(): bool
    {
        // Get what type of Location this is, NULL value is ok for this.
        return EventLocationCrudController::class === $this->getContext()->getCrud()->getControllerFqcn();
    }

    protected function isLaunchPoint(): bool
    {
        // Get what type of Location this is, NULL value is ok for this.
        return LaunchPointCrudController::class === $this->getContext()->getCrud()->getControllerFqcn();
    }
}
