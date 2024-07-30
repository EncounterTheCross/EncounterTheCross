<?php
/**
 * @Author: jwamser
 *
 * @CreateAt: 4/7/23
 * Project: EncounterTheCross
 * File Name: LaunchPointCrudController.php
 */

namespace App\Controller\Admin\Crud;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LaunchPointCrudController extends LocationCrudController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    public function createEntity(string $entityFqcn): Location
    {
        /** @var Location $entity */
        $entity = parent::createEntity($entityFqcn);

        $entity->setType(Location::TYPE_LAUNCH_POINT);

        return $entity;
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

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->findLatLonCordinates($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->findLatLonCordinates($entityInstance);

        // Persist the Entity Now
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Launch Points');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return LocationRepository::queryBuilderFilterByLocationType(Location::TYPE_LAUNCH_POINT, $queryBuilder);
    }

    private function findLatLonCordinates($entityInstance): void
    {
        if ($entityInstance instanceof Location && $this->isLaunchPoint() && Location::TYPE_LAUNCH_POINT === $entityInstance->getType()) {
            if (
                null !== $entityInstance->getGeolocation() && ($entityInstance->getGeolocation()['status'] ?? '') !== 'SUCCESS'
                || null === $entityInstance->getGeolocation()
            ) {
                try {
                    // Make sure we have the Latitude and Longitude of the Launch Point
                    $response = $this->httpClient->request(Request::METHOD_GET, 'https://nominatim.openstreetmap.org/search?format=json&q='.$entityInstance->getLongAddress(true));

                    $data = $response->toArray();

                    if (0 === count($data)) {
                        throw new Exception('Address did not return a Lat/Long to use');
                    }

                    $entityInstance->setGeolocation([
                        'latitude' => $data[0]['lat'],
                        'longitude' => $data[0]['lon'],
                        'color' => '',
                        'status' => 'SUCCESS',
                    ]);
                } catch (Exception $e) {
                    $entityInstance->setGeolocation([
                        'latitude' => null,
                        'longitude' => null,
                        'status' => 'Failed Getting MetaData from openstreetmap',
                    ]);
                }
            }
        }
    }
}
