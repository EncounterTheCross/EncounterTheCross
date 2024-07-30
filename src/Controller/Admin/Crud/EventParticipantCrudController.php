<?php

namespace App\Controller\Admin\Crud;

use App\Contracts\ExporterContract;
use App\Controller\Admin\Crud\Extended\HasXlsxExporter;
use App\Controller\Admin\Crud\Extended\ParentCrudControllerInterface;
use App\Controller\Admin\Crud\Extended\SubCrudControllerInterface;
use App\Controller\Admin\Crud\Extended\SubCrudTrait;
use App\Entity\EventParticipant;
use App\Entity\Person;
use App\Enum\EventParticipantStatusEnum;
use App\Repository\EventParticipantRepository;
use App\Repository\PersonRepository;
use App\Service\Exporter\XlsExporter;
use App\Service\PersonManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\Response;

class EventParticipantCrudController extends AbstractCrudController implements SubCrudControllerInterface
{
    use SubCrudTrait;
    use HasXlsxExporter;
    protected ?ExporterContract $exporter;

    public function __construct(
        private readonly PersonManager $personManager,
        XlsExporter $exporter,
    ) {
        $this->exporter = $exporter;
    }

    public static function getEntityFqcn(): string
    {
        return EventParticipant::class;
    }

    public static function getEntityRepositoryFqcn(): string
    {
        return EventParticipantRepository::class;
    }

    public function updateParticipantToDuplicate(AdminContext $context)
    {
        $entityInstance = $context->getEntity()->getInstance();
        assert($entityInstance instanceof EventParticipant);
        if ($entityInstance->getStatus() !== EventParticipantStatusEnum::DUPLICATE->value) {
            $response = $this->updateParticipantStatus($context, EventParticipantStatusEnum::DUPLICATE);
            if ($response) {
                return $response;
            }
        }

        $url = $this->getAdminUrlGenerator()
            ->includeReferrer()
            ->set(ParentCrudControllerInterface::PARENT_ID, $entityInstance->getEvent()->getId())
            ->setController(EventParticipantCrudController::class)
            ->setAction(Action::INDEX)
        ;

        return $this->redirect($url->generateUrl());
    }

    public function updateParticipantToDropped(AdminContext $context)
    {
        $entityInstance = $context->getEntity()->getInstance();
        assert($entityInstance instanceof EventParticipant);
        if ($entityInstance->getStatus() !== EventParticipantStatusEnum::DROPPED->value) {
            $response = $this->updateParticipantStatus($context, EventParticipantStatusEnum::DROPPED);
            if ($response) {
                return $response;
            }
        }

        $url = $this->getAdminUrlGenerator()
            ->includeReferrer()
            ->set(ParentCrudControllerInterface::PARENT_ID, $entityInstance->getEvent()->getId())
            ->setController(EventParticipantCrudController::class)
            ->setAction(Action::INDEX)
        ;

        return $this->redirect($url->generateUrl());
    }

    public function updateParticipantToAttending(AdminContext $context)
    {
        $entityInstance = $context->getEntity()->getInstance();
        assert($entityInstance instanceof EventParticipant);
        if ($entityInstance->getStatus() !== EventParticipantStatusEnum::ATTENDING->value) {
            $response = $this->updateParticipantStatus($context, EventParticipantStatusEnum::ATTENDING);
            if ($response) {
                return $response;
            }
        }

        $url = $this->getAdminUrlGenerator()
            ->includeReferrer()
            ->set(ParentCrudControllerInterface::PARENT_ID, $entityInstance->getEvent()->getId())
            ->setController(EventParticipantCrudController::class)
            ->setAction(Action::INDEX)
        ;

        return $this->redirect($url->generateUrl());
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof EventParticipant);

        $person = $this->personManager->update($entityInstance->getPerson(), $entityInstance->isForceNewPerson());
        $entityInstance->setPerson($person);

        // persist to database now
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setSearchFields([
                'status',
                'person.firstName',
                'person.lastName',
                'person.email',
                'person.phone',
                'launchPoint.name',
                'church',
            ])
            ->setDefaultSort([
                'createdAt' => 'DESC',
            ])
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function configureActions(Actions $actions): Actions
    {
        $detailIndexAction = Action::new(Action::INDEX, 'Back to Registrations')
            ->linkToUrl(function (EventParticipant $eventParticipant) {
                return $this->getAdminUrlGenerator()
                    ->includeReferrer()
                    ->set(ParentCrudControllerInterface::PARENT_ID, $eventParticipant->getEvent()->getId())
                    ->setController(EventParticipantCrudController::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
                ;
            });

        $configuredActions = parent::configureActions($actions)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->add(Crud::PAGE_DETAIL, $detailIndexAction)
            ->disable(Action::DELETE, Action::BATCH_DELETE, Action::NEW)
            // Temp disable Edit, till we get the edit forms working correctly
//            ->disable(Action::EDIT)
        ;

        $participantStatusDropped = Action::new('mark_drop')
            ->setLabel('Mark Dropped')
//            ->linkToCrudAction('updateParticipantToDropped')
            ->linkToUrl(function (EventParticipant $eventParticipant) {
                return $this->getAdminUrlGenerator()
                    ->includeReferrer()
                    ->set('entityId', $eventParticipant->getId())
                    ->set(ParentCrudControllerInterface::PARENT_ID, $eventParticipant->getEvent()->getId())
                    ->setController(EventParticipantCrudController::class)
                    ->setAction('updateParticipantToDropped')
                    ->generateUrl()
                ;
            })
            ->displayIf(function (EventParticipant $participant) {
                return $participant->getStatus() !== EventParticipantStatusEnum::DROPPED->value;
            })
        ;

        $participantStatusDuplicate = Action::new('mark_dup')
            ->setLabel('Mark Duplicate')
            ->linkToUrl(function (EventParticipant $eventParticipant) {
                return $this->getAdminUrlGenerator()
                    ->includeReferrer()
                    ->set('entityId', $eventParticipant->getId())
                    ->set(ParentCrudControllerInterface::PARENT_ID, $eventParticipant->getEvent()->getId())
                    ->setController(EventParticipantCrudController::class)
                    ->setAction('updateParticipantToDuplicate')
                    ->generateUrl()
                ;
            })
            ->displayIf(function (EventParticipant $participant) {
                return $participant->getStatus() !== EventParticipantStatusEnum::DUPLICATE->value;
            })
        ;

        $participantStatusAttending = Action::new('mark_attending')
            ->setLabel('Mark Attending')
            ->linkToUrl(function (EventParticipant $eventParticipant) {
                return $this->getAdminUrlGenerator()
                    ->includeReferrer()
                    ->set('entityId', $eventParticipant->getId())
                    ->set(ParentCrudControllerInterface::PARENT_ID, $eventParticipant->getEvent()->getId())
                    ->setController(EventParticipantCrudController::class)
                    ->setAction('updateParticipantToAttending')
                    ->generateUrl()
                ;
            })
            ->displayIf(function (EventParticipant $participant) {
                return $participant->getStatus() !== EventParticipantStatusEnum::ATTENDING->value;
            })
        ;

        $exportAllAction = Action::new('exportAll')
            ->setLabel('Export All')
            ->linkToUrl(function (?EventParticipant $eventParticipant = null) {
                $request = $this->getContext()->getRequest();

                return $this->getAdminUrlGenerator()
                    ->setAll($request->query->all())
                    ->setAction('exportAll')
                    ->generateUrl()
                ;
            })
            ->displayIf(function (?EventParticipant $participant = null) {
                return null !== $this->getAdminUrlGenerator()->get(ParentCrudControllerInterface::PARENT_ID);
            })
            ->addCssClass('btn btn-success')
            ->setIcon('fa fa-download')
            ->createAsGlobalAction()
        ;
        $exportLaunchAction = Action::new('exportLaunch')
            ->setLabel('Export (By Launch)')
            ->linkToUrl(function (?EventParticipant $eventParticipant = null) {
                $request = $this->getContext()->getRequest();

                return $this->getAdminUrlGenerator()
                    ->setAll($request->query->all())
                    ->setAction('exportByLaunch')
                    ->generateUrl()
                ;
            })
            ->displayIf(function (?EventParticipant $participant = null) {
                return null !== $this->getAdminUrlGenerator()->get(ParentCrudControllerInterface::PARENT_ID);
            })
            ->addCssClass('btn btn-success')
            ->setIcon('fa fa-download')
            ->createAsGlobalAction()
        ;

        $configuredActions
            ->add(Action::INDEX, $exportAllAction)
            ->add(Action::INDEX, $exportLaunchAction)
            ->add(Action::INDEX, $participantStatusDuplicate)
            ->add(Action::DETAIL, $participantStatusDuplicate)
            ->add(Action::INDEX, $participantStatusAttending)
            ->add(Action::DETAIL, $participantStatusAttending)
            ->add(Action::INDEX, $participantStatusDropped)
            ->add(Action::DETAIL, $participantStatusDropped)
        ;

        // setup permission to global actions
        $configuredActions
            ->setPermissions([
                'mark_attending' => 'ROLE_DATA_EDITOR_OVERWRITE',
                'mark_dup' => 'ROLE_DATA_EDITOR_OVERWRITE',
                'mark_drop' => 'ROLE_DATA_EDITOR_OVERWRITE',
                Action::EDIT => 'ROLE_DATA_EDITOR_OVERWRITE',
            ])
        ;

        return $configuredActions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $statusArray = array_map(function (EventParticipantStatusEnum $status): string {
            return $status->value;
        }, EventParticipantStatusEnum::cases());
        $statusFilter = ChoiceFilter::new('status');
        $statusFilter->setChoices(array_change_key_case(
            array_combine($statusArray, $statusArray),
            CASE_UPPER
        ));

        return parent::configureFilters($filters)
            ->add($statusFilter)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var ?EventParticipant $entity */
        $entity = $this->getContext()?->getEntity()->getInstance();
        // Core
        yield FormField::addColumn('col-lg-8');
        yield FormField::addFieldset('Participant Details');
        yield BooleanField::new('forceNewPerson')
            ->onlyOnForms()
            ->hideWhenCreating()
        ;
        yield TextField::new('fullName')
            ->hideOnForm()
        ;
        yield TextField::new('person.firstName', 'First Name')
            ->hideOnIndex()
            ->hideOnDetail()
        ;
        yield TextField::new('person.lastName', 'Last Name')
            ->hideOnIndex()
            ->hideOnDetail()
//            ->setFormTypeOptions([
//                'query_builder' => function (PersonRepository $personRepository) {
//                    return $personRepository->createQueryBuilder('p')
//                        ->orderBy('p.lastName', 'ASC');
//                },
//            ])
        ;
        yield EmailField::new('person.email', 'Email')
            ->hideOnIndex()
        ;
        yield TelephoneField::new('person.phone', 'Phone')
            ->hideOnIndex()
        ;

        yield Field::new('line1', 'Address')
            ->hideOnIndex()
        ;
        yield Field::new('line2', 'Address 2')
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

        yield FormField::addFieldset('Important');
        yield Field::new('healthConcerns')
            ->hideOnIndex()
        ;
        yield Field::new('questionsOrComments')
            ->hideOnIndex()
        ;

        yield FormField::addColumn('col-lg-4');
        yield FormField::addFieldset('Additionals');
        yield TextField::new('status')
            ->hideOnForm()
            ->setLabel('Attending Status')
        ;
        yield TextField::new('type')
            ->hideOnForm()
        ;
        yield ChoiceField::new('type')
            ->setFormTypeOption(
                ChoiceField::OPTION_CHOICES,
                array_combine(
                    EventParticipant::TYPES(),
                    EventParticipant::TYPES()
                )
            )
            ->onlyOnForms()
        ;
        yield AssociationField::new('event');
        yield BooleanField::new('paid');
        yield TextField::new('paymentMethod')
            ->hideOnForm()
        ;
        yield ChoiceField::new('paymentMethod')
            ->setFormTypeOption(ChoiceField::OPTION_CHOICES, array_combine(
                ['Pay at the door', 'Apply for Scholarship'],
                EventParticipant::PAYMENT_METHODS
            ))
            ->onlyOnForms()
        ;
        yield AssociationField::new('launchPoint');

        // Attendee
        // TODO: Contact Person Lookup when edited...
        // TODO: Invited By Person Lookup??

        if ($entity && EventParticipant::TYPE_ATTENDEE === $entity->getType()) {
            yield FormField::addFieldset('Attendee Contact Details');
            yield Field::new('attendeeContactPerson.details', 'Contact Person')
                ->hideOnIndex()
                ->hideOnForm()
            ;

            yield TextField::new('attendeeContactPerson.details.firstName', 'First Name')
                ->hideOnIndex()
                ->hideOnDetail()
            ;
            yield TextField::new('attendeeContactPerson.details.lastName', 'Last Name')
                ->hideOnIndex()
                ->hideOnDetail()
            ;

            yield Field::new('attendeeContactPerson.relationship', 'Contact Relationship')
                ->hideOnIndex()
            ;
            yield TelephoneField::new('attendeeContactPerson.details.phone', 'Contact Phone')
                ->hideOnIndex()
            ;

            yield FormField::addFieldset('Attendee Details');
            yield Field::new('invitedBy')
                ->hideOnIndex()
            ;
            yield Field::new('church')
                ->hideOnIndex()
            ;
        }

        // Server
        if ($entity && EventParticipant::TYPE_SERVER === $entity->getType()) {
            yield FormField::addFieldset('Serving Details');
            yield Field::new('serverAttendedTimes')
                ->hideOnIndex()
            ;
        }
    }

    private function updateParticipantStatus(AdminContext $context, EventParticipantStatusEnum $status): ?Response
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        // check permissions
        // TODO: will need custom action to allow Leaders to do this
        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => $context->getEntity()])) {
            throw new ForbiddenActionException($context);
        }
        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $entityInstance = $context->getEntity()->getInstance();
        assert($entityInstance instanceof EventParticipant);
        $entityInstance->setStatus($status->value);

        $event = new BeforeEntityUpdatedEvent($entityInstance);
        $this->container->get('event_dispatcher')->dispatch($event);
        $entityInstance = $event->getEntityInstance();

        $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

        $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

        return null;
    }
}
