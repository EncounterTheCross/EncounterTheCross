<?php

namespace App\Tests\Integration\Service\Exporter;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Service\Exporter\XlsExporter;
use App\Tests\IntegrationTester;
use Codeception\Module\Doctrine;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\MockObject\MockObject;
use Yectep\PhpSpreadsheetBundle\Factory;

class XlsExporterTest extends \Codeception\Test\Unit
{
    protected IntegrationTester $tester;

    public function testExportAllHeaders()
    {
        $doctrine = $this->getDoctrine();

        $requiredHeaders = [
            'type',
            'name',
            'email',
            'phone',
            'address',
            'contactPerson',
            'contactRelation',
            'contactPhone',
            'invitedBy',
            'primaryChurch',
            'servedTimes',
            'concerns?',
            'questions',
            'launchPoint',
            'paid',
            'paymentMethod',
        ];
        $events = $doctrine->grabEntitiesFromRepository(Event::class);
        $participants = $doctrine->grabEntitiesFromRepository(EventParticipant::class, ['event' => $events[0]]);

        foreach (array_keys(array_combine($requiredHeaders, $requiredHeaders)) as $header) {
            $this->assertArrayHasKey($header, $participants[0]->getExtendedSerialization());
        }
    }

    public function testExportByLaunchPointHeaders()
    {
        $doctrine = $this->getDoctrine();

        $requiredHeaders = [
            'type',
            'name',
            'email',
            'phone',
            'contactPerson',
            'contactRelation',
            'contactPhone',
            'invitedBy',
            'paid',
            'paymentMethod',
        ];
        $events = $doctrine->grabEntitiesFromRepository(Event::class);
        $participants = $doctrine->grabEntitiesFromRepository(EventParticipant::class, ['event' => $events[0]]);

        foreach (array_keys(array_combine($requiredHeaders, $requiredHeaders)) as $header) {
            $this->assertArrayHasKey($header, $participants[0]->getBasicSerialization());
        }
    }

    public function testGenerateExportAllFile()
    {
        $spreadsheetFactory = $this->mockSpreadSheetFactory();
        $doctrine = $this->getDoctrine();

        $events = $doctrine->grabEntitiesFromRepository(Event::class);
        $participants = $doctrine->grabEntitiesFromRepository(EventParticipant::class, ['event' => $events[0]]);

        $xlsExporter = new XlsExporter($spreadsheetFactory);
        $exportFile = $xlsExporter->createEventReport($participants);

        // Make sure we get the file back
        $this->assertTrue($exportFile instanceof Spreadsheet);
    }

    // tests
    public function testGenerateExportFileByLaunchPoint()
    {
        $spreadsheetFactory = $this->mockSpreadSheetFactory();
        $doctrine = $this->getDoctrine();

        $events = $doctrine->grabEntitiesFromRepository(Event::class);
        $participants = $doctrine->grabEntitiesFromRepository(EventParticipant::class, ['event' => $events[0]]);

        $xlsExporter = new XlsExporter($spreadsheetFactory);
        $exportFile = $xlsExporter->createEventReportByLaunchPoint($participants);

        // Make sure we get the file back
        $this->assertTrue($exportFile instanceof Spreadsheet);
    }

    protected function mockSpreadSheetFactory(): MockObject|Factory
    {
        $spreadsheetFactory = $this->createMock(Factory::class);
        $spreadsheetFactory
            ->expects($this->once())
            ->method('createSpreadsheet')
            ->willReturn(new Spreadsheet())
        ;

        return $spreadsheetFactory;
    }

    protected function getDoctrine(): Doctrine
    {
        $doctrine = $this->getModule('Doctrine');
        if (!$doctrine instanceof Doctrine) {
            $this->fail('Doctrine Module Not Returned!');
        }

        return $doctrine;
    }
}
