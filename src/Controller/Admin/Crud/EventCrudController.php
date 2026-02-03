<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Crud\Extended\HasPrayerTeamAssignmentActionTrait;
use App\Controller\Admin\Crud\Extended\ParentCrudControllerInterface;
use App\Controller\Admin\Crud\Extended\ParentCrudTrait;
use App\Controller\Admin\Crud\Field\Field;
use App\Entity\Event;
use App\Entity\Location;
use App\Enum\EventParticipantStatusEnum;
use App\Field\QrField;
use App\Repository\LocationRepository;
use App\Service\Exporter\XlsExporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\DateFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class EventCrudController extends AbstractCrudController implements ParentCrudControllerInterface
{
    use ParentCrudTrait;
    use HasPrayerTeamAssignmentActionTrait;

    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function createEntity(string $entityFqcn)
    {
        /** @var Event $entity */
        $entity = parent::createEntity($entityFqcn);

        array_map(function (Location $launchPoint) use ($entity) {
            $entity->addLaunchPoint($launchPoint);
        }, $this->locationRepository->getAllActiveLaunchPoints());

        $entity->setCheckInToken(bin2hex(random_bytes(32)));

        return $entity;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('createdAt')
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('data', [
                    'comparison' => ComparisonType::GTE,
                    'value' => (new \DateTime('now'))->setTime(0, 0),
                ])
            )
            ->add(DateTimeFilter::new('start')
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('data', [
                    'comparison' => ComparisonType::GTE,
                    'value' => new \DateTime('today'),
                ])
            )   
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // dynamic settings
        $location = AssociationField::new('location')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                LocationRepository::queryBuilderFilterByLocationType(Location::TYPE_EVENT, $queryBuilder);
            })
        ;
        if (Crud::PAGE_NEW !== $pageName) {
            $location
                ->autocomplete()
                ->setCrudController(EventLocationCrudController::class)
            ;
        } else {
            $location
                // TODO allow adding new locations on event creation
//                ->renderAsEmbeddedForm(LocationCrudController::class)
                ->setFormTypeOption(
                    'placeholder', 'Select Location Type',
                )
            ;
        }
        $launchPoints = AssociationField::new('launchPoints')
//            ->setFormType(ChoiceType::class)
            ->autocomplete()
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                LocationRepository::queryBuilderFilterByLocationType(Location::TYPE_LAUNCH_POINT, $queryBuilder);
            })
        ;

        // return fields
        yield TextField::new('name');
        yield DateField::new('start');
        yield DateField::new('end')
            ->onlyOnForms();
        yield DateField::new('registrationDeadLineServers')
            ->hideOnIndex()
        ;
        yield DateField::new('prayerTeamAssignmentsDeadline')
            ->hideOnIndex()
            ->setHelp('Once this is set, Launch Leaders can start assigning Servers to teams.')
        ;
        yield $location;

        yield $launchPoints;
        yield MoneyField::new('price')
            ->setStoredAsCents(false)
            ->setCurrency('USD')
            ->hideOnIndex()
        ;

        yield IntegerField::new('maxServers')
            ->setLabel('Max Servers')
            ->setHelp('This is the maximum number of servers that can register for this event. Registration team can override and allow more if needed.')
            ->hideOnIndex()
        ;

        yield BooleanField::new('registration_open')
            ->setLabel('Attendee Waitlist Disabled')
            ->setHelp('When unchecked, waitlist will be turned on for attendees. No more servers can register.')
            ->hideOnDetail()
            ->hideOnIndex()
        ;
        yield BooleanField::new('registration_started')
            ->setLabel('Registration Open')
            ->setHelp('When unchecked, no one can register for this event. No waitlist either.')
            ->hideOnDetail()
            ->hideOnIndex();

        yield Field::new('TotalServers')
            ->hideOnForm();
        yield Field::new('TotalAttendees')
            ->hideOnForm();
        yield QrField::new('checkInToken')
            ->setLabel('Check In URL')
            ->hideOnForm()
            ->hideOnIndex()
            ->setPermission('ROLE_DATA_EDITOR_OVERWRITE')
        ;

        yield BooleanField::new('active')
            ->setHelp('Normally you will not need to touch this. Ask Jordan First.')
            ->hideOnDetail()
            ->hideOnIndex()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportByLaunchAction = Action::new('export_attending_by_launch')
//            ->addCssClass('btn btn-success')
//            ->setIcon('fa fa-check-circle')
//            ->displayAsButton()
            ->linkToCrudAction('export')
        ;

        $exportAllAction = Action::new('export_all_attending')
            ->linkToCrudAction('exportAll')
        ;

        $closeRegistration = Action::new('close_registration')
            ->linkToCrudAction('toggleRegistration')
            ->displayIf(function (Event $event) {
                if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE')) {
                    return false;
                }

                if (!$event->isRegistrationOpen()) {
                    return false;
                }

                return new \DateTime() < $event->getStart();
            })
        ;

        $openRegistration = Action::new('open_registration')
            ->linkToCrudAction('toggleRegistration')
            ->displayIf(function (Event $event) {
                if (!$this->isGranted('ROLE_DATA_EDITOR_OVERWRITE')) {
                    return false;
                }

                if ($event->isRegistrationOpen()) {
                    return false;
                }

                return new \DateTime() < $event->getStart();
            })
        ;

        $registrations = Action::new('show_registrations')
            ->linkToCrudAction('redirectToShowSubCrud')
        ;

        $openCheckIn = Action::new('openCheckIn', 'Check-in QR')
            ->linkToCrudAction('openCheckInPage')
            ->addCssClass('btn btn-primary')
            ->displayIf(static function ($entity) {
                return $entity->isActive();
            });

        $serverAssignments = Action::new('server_assignments')
            ->linkToRoute('event_launch_prayer_team_assignments_report', static function ($entity) {
                //                return $entity;
                return ['event' => $entity->getId()];
            });

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportByLaunchAction)
            ->add(Crud::PAGE_INDEX, $exportAllAction)
            ->add(Crud::PAGE_INDEX, $registrations)
            ->add(Crud::PAGE_INDEX, $serverAssignments)
            ->add(Crud::PAGE_DETAIL, $registrations)
            ->add(Crud::PAGE_INDEX, $closeRegistration)
            ->add(Crud::PAGE_INDEX, $openRegistration)
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->setPermissions([
                Action::EDIT => 'ROLE_DATA_EDITOR_OVERWRITE',
                Action::NEW => 'ROLE_DATA_EDITOR_OVERWRITE',
            ])
        ;
    }

    public function toggleRegistration(AdminContext $adminContext, EntityManagerInterface $entityManager, AdminUrlGenerator $urlGenerator): RedirectResponse
    {
        $event = $adminContext->getEntity()->getInstance();

        if (!$event instanceof Event) {
            throw new \LogicException(sprintf('Trying to edit something other than an Event!'));
        }

        $event->setRegistrationOpen(!$event->isRegistrationOpen());
        $entityManager->persist($event);
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Registration has been %s.',
            $event->isRegistrationOpen() ? 'opened' : 'closed'
        ));

        return $this->redirect($urlGenerator->setAction(Action::INDEX)->generateUrl());
    }

    public function exportAll(AdminContext $adminContext, XlsExporter $exporter): StreamedResponse
    {
        $event = $adminContext->getEntity()->getInstance();
        if (!$event instanceof Event) {
            throw new \LogicException('Entity is missing or not an Event');
        }

        $spreadsheet = $exporter->createEventReport(
            array_values($event->getEventParticipants(
                EventParticipantStatusEnum::ATTENDING
            )->toArray())
        );

        return $exporter->streamResponse($spreadsheet);
    }

    public function export(AdminContext $adminContext, XlsExporter $exporter): StreamedResponse
    {
        $event = $adminContext->getEntity()->getInstance();
        if (!$event instanceof Event) {
            throw new \LogicException('Entity is missing or not an Event');
        }

        $spreadsheet = $exporter->createEventReportByLaunchPoint(
            $event->getEventParticipants(
                EventParticipantStatusEnum::ATTENDING
            )->toArray()
        );

        return $exporter->streamResponse($spreadsheet);
    }

    protected function getSubCrudControllerClass(): string
    {
        return EventParticipantCrudController::class;
    }
}
