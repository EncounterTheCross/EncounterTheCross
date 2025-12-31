<?php

namespace App\Service\Exporter;

use App\Contracts\ExporterContract;
use App\Entity\EventParticipant;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use GuzzleHttp\Psr7\MimeType;
use LogicException;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yectep\PhpSpreadsheetBundle\Factory;

class XlsExporter implements ExporterContract
{
    public const EXPORT_ALL = 'EventReport';
    public const EXPORT_ALL_SORTED = 'EventReportByLaunchPoint';

    public function __construct(
        private readonly Factory $spreadsheetFactory,
    ) {
    }

    protected ?QueryBuilder $queryBuilder = null;

    /**
     * @param array|EventParticipant[] $participants
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function createEventReportByLaunchPoint(array $participants): Spreadsheet
    {
        $spreadsheet = $this->spreadsheetFactory->createSpreadsheet();

        // List of launch points as we will have each one have its own sheet
        $launchPoints = [];
        $worksheets = [];
        $exportTime = new DateTime('now', new DateTimeZone('America/Chicago'));

        /** @var EventParticipant $participant */
        foreach ($participants as $participant) {
            // FIX ME: HARD CODE SPAM STATUS TO BE EXCLUDED
            if ($participant->getStatus() === 'spam') {
                continue;
            }

            $worksheetName = $participant->getLaunchPoint()->getName();

            // Create all Worksheets we need, will add to these later
            if (!in_array($worksheetName, array_keys($launchPoints))) {
                $launchPoints[$worksheetName] = []; // this will hold the data we will insert
                $launchPoints[$worksheetName][] = ['Launch Point:', $worksheetName, 'Exported At:', $exportTime->format('d/m/y H:i')];
                $launchPoints[$worksheetName][] = []; // blank row

                $launchPoints[$worksheetName][] = array_keys($participant->getBasicSerialization());
                $worksheets[$worksheetName] = new Worksheet($spreadsheet, strlen($worksheetName) > 27 ? substr($worksheetName, 0, 27) : $worksheetName);
            }

            $launchPoints[$worksheetName][] = $participant->getBasicSerialization();
        }

        foreach ($worksheets as $launchPointName => $worksheet) {
            $spreadsheet->addSheet(
                $worksheet->fromArray($launchPoints[$launchPointName])
            );
        }

        // remove the blank worksheet that was made in the beginning
        $sheetIndex = $spreadsheet->getIndex(
            $spreadsheet->getSheetByName('Worksheet')
        );
        $spreadsheet->removeSheetByIndex($sheetIndex);

        return $spreadsheet;
    }

    /**
     * @param array|EventParticipant[] $participants
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function createEventReport(array $participants): Spreadsheet
    {
        // FIX ME: HARD CODE SPAM STATUS TO BE EXCLUDED
        $participants = array_filter($participants, function (EventParticipant $participant) {
            return $participant->getStatus() !== 'spam';
        });

        $spreadsheet = $this->spreadsheetFactory->createSpreadsheet();
        $worksheet = $spreadsheet->getSheetByName('Worksheet');

        $worksheet->fromArray(array_merge([array_keys($participants[0]->getExtendedSerialization())], array_map(function (EventParticipant $participant) {
            return $participant->getExtendedSerialization();
        }, $participants)));

        return $spreadsheet;
    }

    public function streamResponse(string|Spreadsheet|null $xlsxFileMethod = null): StreamedResponse
    {
        if (is_string($xlsxFileMethod) && !$this->queryBuilder) {
            throw new LogicException('You need to provide a file to stream or set the QueryBuilder.');
        }

        if (is_string($xlsxFileMethod)) {
            // build $xlsx
            $objects = $this->queryBuilder->getQuery()->getResult();
            $method = 'create'.$xlsxFileMethod;
            $xlsxFileMethod = $this->$method($objects);
        }

        return $this->streamSpreadSheetResponse(
            $xlsxFileMethod
        );
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function streamSpreadSheetResponse(
        Spreadsheet $spreadsheet,
        $type = IOFactory::WRITER_XLSX,
        $status = 200,
        $headers = [],
        $writerOptions = [],
    ): StreamedResponse {
        $dispositionHeader = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'Export.xlsx'
        );

        // TODO: should we use Symfony Mime Component to determine the Content-Type?
        // Currently we have the MimeType.php from Guzzle, we will use this for now.
        $headers = array_merge(
            $headers,
            [
                'Content-Disposition' => $dispositionHeader,
                'Content-Type' => MimeType::fromExtension('xlsx'),
                //                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );

        return $this->spreadsheetFactory->createStreamedResponse(
            $spreadsheet,
            $type,
            $status,
            $headers,
            $writerOptions
        );
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }
}
