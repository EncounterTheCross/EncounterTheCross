<?php

namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\DataFixtures\AppFixtures;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Module\Doctrine;
use Codeception\Module\Filesystem;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use Codeception\Util\Locator;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestActions;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestFormAsserts;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestIndexAsserts;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestUrlGeneration;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;

class Functional extends \Codeception\Module
{
    // Easy Admin TestHelpers
    use CrudTestActions;
    use CrudTestIndexAsserts;
    use CrudTestUrlGeneration;

    // Easy Admin Assert Helpers
    use CrudTestIndexAsserts;
    use CrudTestFormAsserts;

    // Database Factories
    use Symfony\DoctrineAssertionsTrait;

    //    protected KernelBrowser $client;

    protected AdminUrlGeneratorInterface $adminUrlGenerator;
    protected EntityManagerInterface $entityManager;

    /**
     * @throws ModuleException
     */
    public function _beforeSuite(array $settings = [])
    {
        parent::_beforeSuite($settings);

        /** @var Doctrine $doctrine */
        $doctrine = $this->getModule('Doctrine');
        $doctrine->loadFixtures(AppFixtures::class, false);
    }

    public function _before(TestInterface $test)
    {
        parent::_before($test);
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        if ($symfony->_getContainer()->has(AdminUrlGenerator::class)) {
            $this->adminUrlGenerator = $symfony->_getContainer()->get(AdminUrlGenerator::class);
        }
    }

    public function findFirstEntityId()
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');

        /** @var Crawler $table */
        $table = $symfony->_findElements(Locator::firstElement('//table'));
        //        $this->assertNotEmpty($tableBody);
        //        $table = reset($tableBody);
        //        dump($tableBody);
        $this->assertEquals('table', $table->nodeName());
        $tableRow = $table->filter('tbody')->filter('tr');

        return $tableRow->attr('data-id');
    }

    // Index Generation
    public function amOnAdminIndexPageFor(string $dashboard, string $controller, ?string $query = null)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $symfony->amOnPage(
            $this->generateIndexUrl($query, $dashboard, $controller)
        );
        $symfony->seeResponseCodeIsSuccessful();
    }

    // New Form Generation
    public function amOnAdminNewFormPageFor(string $dashboard, string $controller)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $symfony->amOnPage(
            $this->generateNewFormUrl($dashboard, $controller)
        );
        $symfony->seeResponseCodeIsSuccessful();
    }

    // Edit Form Generation
    public function amOnAdminEditFormPageFor(string $dashboard, string $controller, ?string $query = null)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $symfony->amOnPage(
            $this->generateEditFormUrl($query, $dashboard, $controller)
        );
        $symfony->seeResponseCodeIsSuccessful();
    }

    // Detail Generation
    public function amOnAdminDetailPageFor(string $dashboard, string $controller, ?string $query = null)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $symfony->amOnPage(
            $this->generateDetailUrl($query, $dashboard, $controller)
        );
        $symfony->seeResponseCodeIsSuccessful();
    }

    // Filter Render Generation
    public function amOnAdminFilterRenderPageFor(string $dashboard, string $controller)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $symfony->amOnPage(
            $this->generateFilterRenderUrl($dashboard, $controller)
        );
        $symfony->seeResponseCodeIsSuccessful();
    }

    public function _saveFileToOutputDirectory($binaryContent, $filename)
    {
        // Get the output directory from Codeception's configuration
        $outputDir = Configuration::outputDir();

        // Full path where the file will be saved
        $fullPath = $outputDir.$filename;

        // Write the content to the file
        file_put_contents($fullPath, $binaryContent);

        $this->debug('File saved to: '.$fullPath);
    }

    public function receiveFileResponse(string $actionSelector)
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');

        /** @var Crawler $actionElement */
        $actionElement = $symfony->_findElements($actionSelector);

        $href = $actionElement->extract(['href']);
        $this->assertIsArray($href);
        $href = reset($href);

        $actionResponse = $symfony->_request(Request::METHOD_GET, $href);
        $this->_saveFileToOutputDirectory($actionResponse, 'Export.xlsx');
    }

    public function getSpreedSheetTabCount(string $filename): int
    {
        // Get the output directory from Codeception's configuration
        $outputDir = Configuration::outputDir();

        // Full path where the file will be saved
        $fullPath = $outputDir.$filename;

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($fullPath);

        return $spreadsheet->getSheetCount();
    }

    public function deleteExportFile(string $filename)
    {
        /** @var Filesystem $filesystem */
        $filesystem = $this->getModule('Filesystem');

        // Get the output directory from Codeception's configuration
        $outputDir = Configuration::outputDir();

        // Full path where the file will be saved
        $fullPath = $outputDir.$filename;
        $filesystem->deleteFile($fullPath);
    }
}
