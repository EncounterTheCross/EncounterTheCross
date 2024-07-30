<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Testimonial;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TestimonialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Testimonial::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->setPermissions([
                Action::EDIT => 'ROLE_TESTIMONIAL_REVIEWER',
            ])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextareaField::new('quote');
        yield TextField::new('city');
        yield BooleanField::new('sharable');
        // attendedAt
        yield EmailField::new('email');

        yield BooleanField::new('approved');
        //        return [
        // //            TextField::new('name'),
        //            TextEditorField::new('description'),
        //        ];
    }
}
