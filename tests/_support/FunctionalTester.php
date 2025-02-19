<?php

namespace App\Tests;

use App\Controller\Admin\Crud\EventCrudController;
use App\Controller\Admin\Crud\EventLocationCrudController;
use App\Controller\Admin\Crud\EventParticipantCrudController;
use App\Controller\Admin\Crud\LaunchPointCrudController;
use App\Controller\Admin\Crud\LeaderCrudController;
use App\Controller\Admin\Crud\TestimonialCrudController;
use App\Controller\Admin\MainDashboardController;
use App\Entity\ContactPerson;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\Leader;
use App\Entity\Person;
use App\Exception\FunctionalTestLogicException;
use Codeception\Attribute\Given;
use Codeception\Attribute\Then;
use Codeception\Attribute\When;
use Codeception\Configuration;
use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Locator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestIndexAsserts;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestSelectors;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Test\Factories;

/**
 * Inherited Methods.
 *
 * @method void                    wantToTest($text)
 * @method void                    wantTo($text)
 * @method void                    execute($callable)
 * @method void                    expectTo($prediction)
 * @method void                    expect($prediction)
 * @method void                    amGoingTo($argumentation)
 * @method void                    am($role)
 * @method void                    lookForwardTo($achieveValue)
 * @method void                    comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    // Database Setup
    use Factories;

    // Easy Admin Selectors
    use CrudTestSelectors;
    use CrudTestIndexAsserts;

    // Entities Created For Test
    private array $entities = [];

    /**
     * @Given /^I am logged in as a Leader$/
     */
    public function iAmLoggedInAsALeader(): void
    {
        $this->amOnPage('/login');
        $this->seeElement('form');
        $this->submitForm('form', [
            'email' => 'dev@dev.com',
            'password' => 'tada',
        ],
            'button[type="submit"]'
        );

        $this->seeCurrentUrlEquals('/admin');
    }

    /**
     * @Then /^I should not see a[n]? "([^"]*)" action?$/
     */
    public function iShouldNotSeeAnAction($action): void
    {
        $this->dontSeeElement($this->getActionSelector(
            self::createActionClass($action)
        ));
    }

    /**
     * @Given /^I see "([^"]*)" action$/
     *
     * @Then /^I should see a[n]? "([^"]*)" action?$/
     */
    public function iSeeAction($action): void
    {
        $this->seeElement($this->getActionSelector(
            self::createActionClass($action)
        ));
    }

    /**
     * @Given /^I am on the new "([^"]*)" Detail Page$/
     *
     * Use this method to navigate to new data generated in the test
     */
    public function iAmOnTheNewAdminDetailPage($objectType): void
    {
        $subEntity = [
            'Attendee' => EventParticipant::class,
            'Server' => EventParticipant::class,
        ];
        $baseEntityPath = 'App\\Entity\\';
        $subEntityPath = '';
        if (in_array($objectType, array_keys($subEntity))) {
            $objectType = $subEntity[$objectType];
            $baseEntityPath = '';
            $subEntityPath = '--'.$objectType;
        }
        if (!array_key_exists($baseEntityPath.$objectType.$subEntityPath, $this->entities)) {
            $this->fail(sprintf(
                'The %s objectType does not have a new instance to use.',
                $objectType
            ));
        }

        $this->iAmOnTheDetailPage(
            $objectType,
            $this->entities[$baseEntityPath.$objectType.$subEntityPath][0]->getId()
        );
    }

    /**
     * @Given /^I am on the "([^"]*)" "([^"]*)" Page$/
     *
     * Use this method for data that was generated in DoctrineFixtures
     */
    public function iAmOnTheAdminPage($objectType, $page): void
    {
        // TODO: This will need adjusted for the other admin pages as well

        // Go to the list page before going to the details page
        $this->iAmOnTheListPage($objectType);

        if (in_array(strtolower($page), ['detail', 'details'])) {
            $this->iAmOnTheDetailPage($objectType);
        }
    }

    protected function iAmOnTheListPage($objectType): void
    {
        match ($objectType) {
            'Events' => $this->amOnAdminIndexPageFor(
                MainDashboardController::class,
                EventCrudController::class
            ),
            'Event Locations' => $this->amOnAdminIndexPageFor(
                MainDashboardController::class,
                EventLocationCrudController::class
            ),
            'Launch Points' => $this->amOnAdminIndexPageFor(
                MainDashboardController::class,
                LaunchPointCrudController::class
            ),
            'Testimonies' => $this->amOnAdminIndexPageFor(
                MainDashboardController::class,
                TestimonialCrudController::class
            ),
            'Leaders' => $this->amOnAdminIndexPageFor(
                MainDashboardController::class,
                LeaderCrudController::class
            ),
            default => throw new TestRuntimeException(sprintf('No object called %s, found in an Admin page.', $objectType)),
        };

        $this->seeInCurrentUrl('crudAction=index');
    }

    /**
     * @When /^I click on the action menu for a[n]? "([^"]*)"$/
     *
     * @Given /^I click on the action menu for the new "([^"]*)"$/
     *
     * @When /^I click on the action row menu$/
     */
    public function iClickOnTheActionMenuForAn($objectType = null): void
    {
        $locator = Locator::combine('tbody', Locator::firstElement('tr'));
        //        if (null !== $objectType) {
        //            $subEntity = [
        //                'Attendee' => EventParticipant::class,
        //                'Server' => EventParticipant::class,
        //            ];
        //            $baseEntityPath = 'App\\Entity\\';
        //            $subEntityPath = '';
        //            if (in_array($objectType, array_keys($subEntity))) {
        //                $objectType = $subEntity[$objectType];
        //                $baseEntityPath = '';
        //                $subEntityPath = '--'.$objectType;
        //
        //            }
        //            if (!array_key_exists($baseEntityPath.$objectType.$subEntityPath,$this->entities)) {
        //                $this->fail(sprintf(
        //                    'The %s objectType does not have a new instance to use.',
        //                    $objectType
        //                ));
        //            }
        //
        //            $object = $this->entities[$baseEntityPath.$objectType.$subEntityPath][0] ?? $this->fail(sprintf(
        //                'The %s objectType does not have a new instance to use.',
        //                $objectType
        //            ));
        //
        // //            $context = $this->getIndexEntityActionSelector('detail', $object->getId());
        //            $row = $this->getIndexEntityRowSelector($object->getId());
        //            // //*[@id="main"]/table/tbody/tr[1]/td[9]/div/a
        //            // #main > table > tbody > tr:nth-child(1) > td.actions.actions-as-dropdown > div > a
        //            $locator = $row .' td.actions.actions-as-dropdown div a[data-bs-toggle="dropdown"]';
        //
        //        }

        $this->seeElement($locator);
        $this->click($locator);
    }

    protected function iAmOnTheDetailPage($objectType, $id = null): void
    {
        match ($objectType) {
            'Events' => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                EventCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            'Attendee', 'Server', EventParticipant::class => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                EventParticipantCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            'Event Locations' => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                EventLocationCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            'Launch Points' => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                LaunchPointCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            'Testimonies' => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                TestimonialCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            'Leaders' => $this->amOnAdminDetailPageFor(
                MainDashboardController::class,
                LeaderCrudController::class,
                $id ?? $this->findFirstEntityId()
            ),
            default => throw new TestRuntimeException(sprintf('No object called %s, found in an Admin page.', $objectType)),
        };

        $this->seeInCurrentUrl('crudAction=detail');
    }

    /**
     * @When /^I click and download the "([^"]*)" action$/
     */
    public function iClickAndDownloadTheAction($action): void
    {
        $this->receiveFileResponse($this->getActionSelector(
            self::createActionClass($action)
        ));
    }

    /**
     * @When /^I click and download the global "([^"]*)" action$/
     */
    public function iClickAndDownloadTheGlobalAction($action): void
    {
        $this->receiveFileResponse($this->getGlobalActionSelector(
            self::createCamelCaseActionClass($action)
        ));
    }

    /**
     * @When /^I click the global "([^"]*)" action$/
     */
    public function iClickTheGlobalAction($action): void
    {
        $this->click($this->getGlobalActionSelector(
            self::createCamelCaseActionClass($action)
        ));
    }

    /**
     * @When /^I click the "([^"]*)" action$/
     */
    public function iClickTheAction($action): void
    {
        $this->click($this->getActionSelector(
            self::createActionClass($action)
        ));
    }

    /**
     * @Then /^I receive the xlsx file$/
     */
    public function iReceiveTheXlsxFile(): void
    {
        $this->seeFileFound('Export.xlsx', Configuration::outputDir());
    }

    /**
     * @Given /^I verify there are tabs$/
     */
    public function iVerifyThereAreTabs(): void
    {
        $sheetCount = $this->getSpreedSheetTabCount('Export.xlsx');

        $this->assertGreaterThan(1, $sheetCount);
        $this->deleteExportFile('Export.xlsx');
    }

    /**
     * @Given /^I verify there are not tabs$/
     */
    public function iVerifyThereAreNotTabs(): void
    {
        $sheetCount = $this->getSpreedSheetTabCount('Export.xlsx');

        $this->assertLessOrEquals(1, $sheetCount);
        $this->deleteExportFile('Export.xlsx');
    }

    private static function createActionClass($action): string
    {
        return strtolower(str_replace(' ', '_', $action));
    }

    private static function createCamelCaseActionClass(string $action): string
    {
        return lcfirst(str_replace(
            '_',
            '',
            ucwords(
                str_replace(' ', '_', $action)
            )
        ));
    }

    /**
     * @Then /^I should see Form Field label of "([^"]*)"$/
     */
    public function iShouldSeeFormFieldDisplayOf($fieldLabel): void
    {
        $this->see($fieldLabel);
    }

    /**
     * @Then /^I should be on "([^"]*)" "([^"]*)" Page$/
     */
    public function iShouldBeOnPage($entityCrud, $page): void
    {
        $crudActionPart = 'crudAction=';
        $crudActionPart .= match (strtolower($page)) {
            'list' => Crud::PAGE_INDEX,
            'show' => Crud::PAGE_DETAIL,
            'edit' => Crud::PAGE_EDIT,
            'new' => Crud::PAGE_NEW,

            default => $this->fail('Not able to verifiy all the options'),
        };

        $this->seeInCurrentUrl($crudActionPart);

        // crudControllerFqcn=App%5CController%5CAdmin%5CCrud%5CEventParticipantCrudController
        $crudControllerPart = 'crudControllerFqcn=';
        $crudControllerPartClass = match ($entityCrud) {
            'Events' => EventCrudController::class,
            'Event Participants' => EventParticipantCrudController::class,
            'Leaders' => LeaderCrudController::class,
            'Launch Points' => LaunchPointCrudController::class,
            default => $this->fail('Not able to identify the Entity for the CRUD.'),
        };

        $this->seeInCurrentUrl($crudControllerPart.urlencode($crudControllerPartClass));
    }

    /**
     * @Given /^I have a new "([^"]*)"$/
     * @Given /^I have a new "([^"]*)" with email "([^"]*)"$/
     */
    public function iHaveInDatabase($entity, $email = null): void
    {
        $subEntity = [
            'Attendee' => EventParticipant::class,
            'Server' => EventParticipant::class,
        ];
        $baseEntityPath = 'App\\Entity\\';
        $subEntityPath = '';
        $attributes = [
            'name' => 'I made this in a test',
        ];

        if (strtolower($entity) === 'event') {
            $attributes = [
                'registrationStarted' => true,
            ];
        }

        if (in_array($entity, array_keys($subEntity))) {
            $attributes = match ($entity) {
                'Server' => $this->getServerAttributes(),
                'Attendee' => $this->getAttendeeAttributes(),
                default => [],
            };
            $entity = $subEntity[$entity];
            $baseEntityPath = '';
            $subEntityPath = '--'.$entity;
        }

        if (!class_exists($baseEntityPath.$entity)) {
            $this->fail(sprintf(
                '%s Entity does not exist.',
                $baseEntityPath.$entity
            ));
        }

        if (null !== $email) {
            $attributes = array_merge(
                $attributes,
                ['person.email' => $email]
            );
        }

        $entityObject = $this->have($baseEntityPath.$entity, $attributes);


        $this->entities[$baseEntityPath.$entity.$subEntityPath][] = $entityObject;
    }

    private function getAttendeeAttributes(): array
    {
        $contactPerson = $this->entities[ContactPerson::class][] = $this->have(ContactPerson::class);

        return [
            'type' => EventParticipant::TYPE_ATTENDEE,
            'attendeeContactPerson' => $contactPerson,
            'event' => $this->entities[Event::class][0],
        ];
    }

    private function getServerAttributes(): array
    {
        return [
            'type' => EventParticipant::TYPE_SERVER,
            'attendeeContactPerson' => null,
            'event' => $this->entities[Event::class][0],
        ];
    }

    /**
     * @Given /^I am on an Event "([^"]*)" registration$/
     */
    public function iAmOnRegistration($attendeeType): void
    {
        if (
            !array_key_exists(Event::class, $this->entities)
            || !array_key_exists(0, $this->entities[Event::class])
        ) {
            throw new FunctionalTestLogicException(sprintf('Expected Entity not configured for the test. Expected a new `%s` to be created before running.', Event::class));
        }
        /** @var Event $event */
        $event = $this->entities[Event::class][0];

        if (!in_array(strtolower($attendeeType), ['attendee', 'server'])) {
            throw new FunctionalTestLogicException(sprintf('Expected "attendee" or "server" as the event type, recieved %s', $attendeeType));
        }

        $this->amOnRoute(sprintf(
            'app_registration_%s_formentry',
            strtolower($attendeeType)
        ), [
            'event' => $event->getId(),
        ]);
    }

    /**
     * @Given /^I fill out registration with "([^"]*)" info$/
     */
    public function iFillOutRegistrationWithInfo($attendeeType): void
    {
        if (!in_array(strtolower($attendeeType), ['attendee', 'server'])) {
            throw new FunctionalTestLogicException(sprintf('Expected "attendee" or "server" as the event type, recieved %s', $attendeeType));
        }
        $faker = Factory::faker();

        // ** Attendee Information
        // firstname
        $this->fillField(['name' => 'attendee_event_participant[person][firstName]'], $faker->firstName());
        // lastname
        $this->fillField(['name' => 'attendee_event_participant[person][lastName]'], $faker->lastName());
        // email
        $this->fillField(['name' => 'attendee_event_participant[person][email]'], $faker->email());
        // phone
        $this->fillField(['name' => 'attendee_event_participant[person][phone]'], $faker->phoneNumber());
        // address
        $this->fillField(['name' => 'attendee_event_participant[line1]'], $faker->streetAddress());
        // city
        $this->fillField(['name' => 'attendee_event_participant[city]'], $faker->city());
        // state
        $this->selectOption('form[name=attendee_event_participant] select[name="attendee_event_participant[state]"]', ['value' => 'KS']);
        // zipcode
        $this->fillField(['name' => 'attendee_event_participant[zipcode]'], $faker->postcode());

        // ** Contact Person Information
        // firstname
        $this->fillField(['name' => 'attendee_event_participant[attendeeContactPerson][details][firstName]'], $faker->firstName());
        // lastname
        $this->fillField(['name' => 'attendee_event_participant[attendeeContactPerson][details][lastName]'], $faker->lastName());
        // phone
        $this->fillField(['name' => 'attendee_event_participant[attendeeContactPerson][details][phone]'], $faker->phoneNumber());
        // relation
        $this->selectOption('form[name=attendee_event_participant] input[name="attendee_event_participant[attendeeContactPerson][relationship]"]', ['value' => 'Mother']);

        // ** Questions
        // launchpoint
        if (
            !array_key_exists(Event::class, $this->entities)
            || !array_key_exists(0, $this->entities[Event::class])
        ) {
            throw new FunctionalTestLogicException(sprintf('Expected Entity not configured for the test. Expected a new `%s` to be created before running.', Event::class));
        }
        /** @var Event $event */
        $event = $this->entities[Event::class][0];

        $launchpoints = $event->getLaunchPoints()->getIterator();
        $launchpoint = $faker->randomElement($launchpoints);
        $this->selectOption('form[name=attendee_event_participant] select[name="attendee_event_participant[launchPoint]"]', ['value' => (string) $launchpoint->getId()]);

        // invitedby
        $this->fillField(['name' => 'attendee_event_participant[invitedBy]'], $faker->name());
        // church
        $this->fillField(['name' => 'attendee_event_participant[church]'], $faker->company());
        // concerns
        $this->fillField(['name' => 'attendee_event_participant[healthConcerns]'], $faker->paragraph());
        // questions
        $this->fillField(['name' => 'attendee_event_participant[questionsOrComments]'], $faker->paragraph());
        // payment
        $this->selectOption('form[name=attendee_event_participant] select[name="attendee_event_participant[paymentMethod]"]', ['value' => 'SCHOLARSHIP']);
    }

    /**
     * @Given /^I fill out registration attendee email with "([^"]*)"$/
     */
    public function iFillOutRegistrationAttendeeEmailWith($email): void
    {
        // email
        $this->fillField(['name' => 'attendee_event_participant[person][email]'], $email);
    }

    /**
     * @When /^I submit registration$/
     */
    public function iSubmitRegistration(): void
    {
        $this->click('Register!');
    }

    /**
     * @Then /^I should see "([^"]*)" page$/
     */
    public function iShouldSeePage($arg1): void
    {
        $this->see('Thank You! Registration Completed.');
    }

    /**
     * @Given /^I should have (\d+) "([^"]*)" in database with different "([^"]*)"$/
     */
    public function iShouldHaveInDatabaseWithDifferent($quantity, $entityType, $uniqueField): void
    {
        if (
            !array_key_exists(Event::class, $this->entities)
            || !array_key_exists(0, $this->entities[Event::class])
        ) {
            throw new FunctionalTestLogicException(sprintf('Expected Entity not configured for the test. Expected a new `%s` to be created before running.', Event::class));
        }
        /** @var Event $event */
        $event = $this->entities[Event::class][0];

        $repoResponse = match ($entityType) {
            'persons' => $this->grabEntitiesFromRepository(Person::class, ['email' => 'a@a.com']),
        };

        $this->assertTrue((int) $quantity === count($repoResponse), sprintf(
            'Expected %d, got %d responses.',
            $quantity,
            count($repoResponse)
        ));
        $field = 'get'.$uniqueField;
        $uniqueFields = [];
        foreach ($repoResponse as $item) {
            $uniqueFields[] = $item->$field();
        }

        $this->assertTrue(count($uniqueFields) === count(array_unique($uniqueFields)));
    }
}
