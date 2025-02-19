<?php

declare(strict_types=1);

namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\DataFixtures\AppFixtures;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Module\Doctrine;

class Integration extends \Codeception\Module
{
    /**
     * @throws ModuleException
     * @throws ModuleRequireException
     */
    public function _beforeSuite(array $settings = []): void
    {
        parent::_beforeSuite($settings);

        $doctrine = $this->getModule('Doctrine');
        if ($doctrine instanceof Doctrine) {
            $doctrine->loadFixtures(AppFixtures::class, false);
        }
    }
}
