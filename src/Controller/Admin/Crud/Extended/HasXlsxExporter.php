<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/20/24
 * Project: EncounterTheCross
 * File Name: hasExportor.php
 */

namespace App\Controller\Admin\Crud\Extended;

use App\Contracts\ExporterContract;
use App\Exception\Core\RuntimeException;
use App\Service\Exporter\XlsExporter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\AssetsDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasXlsxExporter
{
    /**
     * This is not type hinted do to class using that has extention.
     *
     * @see AbstractController
     *
     * @var ContainerInterface
     */
    protected $container;

    abstract public function configureFields(string $pageName): iterable;

    abstract protected function getFieldAssets(FieldCollection $fieldDtos): AssetsDto;

    abstract public function createIndexQueryBuilder(
        SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters,
    ): QueryBuilder;

    protected function getExporter(): ExporterContract
    {
        if (null === ($this->exporter ?? null)) {
            throw new RuntimeException(sprintf('You never setup an Exporter for this CRUD. Please include an Exporter implementing %s, on the exporter property.', ExporterContract::class));
        }

        return $this->exporter;
    }

    public function exportAll(AdminContext $context): StreamedResponse
    {
        $filteredQueryBuilder = $this->getFilteredQueryBuilder($context);
        $this->exporter->setQueryBuilder($filteredQueryBuilder);

        return $this->getExporter()->streamResponse(XlsExporter::EXPORT_ALL);
    }

    public function exportByLaunch(AdminContext $context)
    {
        $filteredQueryBuilder = $this->getFilteredQueryBuilder($context);
        $this->exporter->setQueryBuilder($filteredQueryBuilder);

        return $this->getExporter()->streamResponse(XlsExporter::EXPORT_ALL_SORTED);
    }

    protected function getFilteredQueryBuilder(AdminContext $context): QueryBuilder
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($fields));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());

        return $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
    }
}
