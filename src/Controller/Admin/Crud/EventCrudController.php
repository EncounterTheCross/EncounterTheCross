<?php

namespace App\Controller\Admin\Crud;

use App\Controller\Admin\Crud\Extended\ParentCrudControllerInterface;
use App\Controller\Admin\Crud\Extended\ParentCrudTrait;
use App\Controller\Admin\Crud\Field\Field;
use App\Entity\Event;
use App\Entity\Location;
use App\Enum\EventParticipantStatusEnum;
use App\Repository\LocationRepository;
use App\Service\Exporter\XlsExporter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventCrudController extends AbstractCrudController implements ParentCrudControllerInterface
{
    use ParentCrudTrait;

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

        return $entity;
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
        yield DateField::new('registrationDeadLineServers');
        yield $location;

        yield $launchPoints;
        yield MoneyField::new('price')
            ->setStoredAsCents(false)
            ->setCurrency('USD');
        yield Field::new('TotalServers')
            ->hideOnForm();
        yield Field::new('TotalAttendees')
            ->hideOnForm();
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

        $registrations = Action::new('show_registrations')
            ->linkToCrudAction('redirectToShowSubCrud')
        ;

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportByLaunchAction)
            ->add(Crud::PAGE_INDEX, $exportAllAction)
            ->add(Crud::PAGE_INDEX, $registrations)
            ->add(Crud::PAGE_DETAIL, $registrations)
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->setPermissions([
                Action::EDIT => 'ROLE_DATA_EDITOR_OVERWRITE',
                Action::NEW => 'ROLE_DATA_EDITOR_OVERWRITE',
            ])
        ;
    }

    public function exportAll(AdminContext $adminContext, XlsExporter $exporter): StreamedResponse
    {
        $event = $adminContext->getEntity()->getInstance();
        if (!$event instanceof Event) {
            throw new LogicException('Entity is missing or not an Event');
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
            throw new LogicException('Entity is missing or not an Event');
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
