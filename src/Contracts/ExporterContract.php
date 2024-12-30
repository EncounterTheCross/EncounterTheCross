<?php

/**
 * @Author: jwamser
 *
 * @CreateAt: 1/20/24
 * Project: EncounterTheCross
 * File Name: ExporterContract.php
 */

namespace App\Contracts;

/*
 * TODO: define how we want this to work,
 *  this currently is just to make sure we have an exporter
 */

use Doctrine\ORM\QueryBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExporterContract
{
    //    public function createEventReport(array $participants): \JsonSerializable;

    public function setQueryBuilder(QueryBuilder $queryBuilder): self;

    public function streamResponse(string|Spreadsheet|null $xlsxFileMethod = null): StreamedResponse;
}
