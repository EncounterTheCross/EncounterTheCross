<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\MainDashboardController;
use App\Entity\Leader;
use App\Service\RoleManager\Role;
use App\Service\RoleManager\RoleFormatter;
use App\Service\RoleManager\RoleListFinder;
use Closure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LeaderCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RoleListFinder $roleFinder,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Leader::class;
    }

    public function configureFields(string $pageName): iterable
    {
        //        yield from parent::configureFields($pageName);
        yield AssociationField::new('person', 'Full Name')
            ->renderAsEmbeddedForm(PersonCrudController::class)
        ;
        yield EmailField::new('email');
        yield TextField::new('plainPassword')
            ->setFormTypeOption('label', 'Password')
            ->onlyOnForms()
            ->hideWhenUpdating()
        ;
        yield DateField::new('updatedAt')
            ->hideOnForm();
        yield DateField::new('createdAt')
            ->onlyOnDetail();

        /*
         * How we want to display the options list for roles
         *
         * !!!If Role::ADMIN is not the role right under Role::SUPER_ADMIN we will want to redo this logic!!!
         *
         * New -> ROLE::FULL = that and down , Role::LIMITED_FULL = that and down
         * Index -> only what is assigned
         * Edit -> ROLE::FULL = that and down , Role::LIMITED_FULL = that and down
         * Details -> only what is assigned
         *
         * This list is to determine what roles the current user can assign to the current Leader Object
         * Default List = $securityUserRoles
         */
        if ($this->isGranted(Role::SUPER_ADMIN)) {
            $roles = Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName ?
                $this->roleFinder->getRolesAccessableToUserOrFullList($this->getUser())// is either an ADMIN or SUPER_ADMIN
                : $this->getInstancesRoles();
            yield ChoiceField::new('roles')
                ->setChoices(
                    RoleFormatter::formatRolesForForm($roles)
                )
                ->hideOnDetail()
                ->hideOnIndex()
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges()
            ;
            yield ChoiceField::new('roles', 'Permission Groups')
                ->hideOnForm()
                ->renderAsBadges()
                ->setChoices(
                    function (?Leader $leader, FieldDto $fieldDto): array {
                        $options = $this->roleFinder->getRolesAccessableToUserOrFullList(
                            $leader
                        );

                        return array_combine($options, $options);
                    }
                )
            ;
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::new('impersonate', 'Impersonate')
                ->linkToUrl(function (?Leader $entity = null): string {
                    return $this->getAdminUrlGenerator()
                        ->unsetAll()
                        ->set('_switch_user', $entity->getEmail())
                        ->setDashboard(MainDashboardController::class)
//                        ->
                        ->generateUrl()
                    ;
                })
                ->displayIf(function (?Leader $entity): bool {
                    $loggedInLeader = $this->getUser();
                    assert($loggedInLeader instanceof Leader);

                    return $entity->getEmail() && $entity->getEmail() !== $loggedInLeader->getEmail() && $this->isGranted(Role::FULL);
                })
            )
            ->setPermissions([
                Action::EDIT => 'EDIT',
            ])
            ->setPermission(Action::NEW, 'ROLE_DATA_EDITOR_OVERWRITE')
//            ->setPermission(Action::EDIT,Role::FULL)
        ;
        // TODO: Add invitation Action for new Leaders
        //            ->remove(Crud::PAGE_INDEX,Crud::PAGE_NEW);
    }

    /**
     * Use this to get Full list if editing, or assigned list if displaying.
     */
    private function getInstancesRoles(): array
    {
        // Get Leaders roles if instance is not null,
        // should only be null if instance was never created before.
        // Null would be edge case and I can say for now defaulting this to a
        // normal admin here would be fine. May change later
        $instanceRoles = $this->getContext()->getEntity()->getInstance()?->getRoles();

        // if instance was null then return Role set to a User with no assigned roles
        return $instanceRoles ?? [Role::USER];
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    //    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    //    {
    //        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
    //        return $this->addPasswordEventListener($formBuilder);
    //    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword(): Closure
    {
        return function ($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }

            $password = $form->get('plainPassword')->getData();
            if (null === $password) {
                $form->getData()
                    // This will work for us if this is only happening on create.
                    ->setPassword(md5(rand()))
                    ->eraseCredentials()
                ;

                return;
            }

            $hash = $this->passwordHasher->hashPassword($this->getUser(), $password);
            $form->getData()
                ->setPassword($hash)
                ->eraseCredentials()
            ;
        };
    }
}
